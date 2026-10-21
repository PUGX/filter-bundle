<?php

namespace PUGX\FilterBundle;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Forms are kept only for the current request: reset() clears them, so the service
 * holds no state across requests in long-running processes (e.g. worker mode).
 */
final class Filter implements ResetInterface
{
    // Request::METHOD_QUERY is not available before Symfony 7.4
    private const METHOD_QUERY = 'QUERY';

    /** @var array<string, FormInterface> */
    private array $forms = [];

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Perform actual filtering. You need to pass an identifying name.
     * You'll get an array with name of fields as keys and the filtered values
     * as values (except for "_sort" key, which holds info for sorting).
     *
     * @return array<string, mixed>
     */
    public function filter(string $name): array
    {
        $filter = [];
        /** @var array<string, mixed>|null $values */
        $values = $this->getSession()->get('filter.'.$name);
        if (null !== $values) {
            if ($this->forms[$name]->isSubmitted() || $this->forms[$name]->submit($values)->isValid()) {
                $filter = \array_filter($values, static fn ($value): bool => '' !== $value);
            }
        }
        if ([] !== ($sort = $this->getSort($name))) {
            $filter['_sort'] = $sort;
        }

        return $filter;
    }

    /**
     * Get the value of a single form field.
     */
    public function getFormData(string $name, string $field): mixed
    {
        /** @var array<string, mixed> $data */
        $data = $this->getForm($name)->getData();

        return $data[$field];
    }

    /**
     * Get the form object to pass to a template.
     * You can pass an optional type, if you want to ensure that a view
     * of the same form type is returned.
     */
    public function getFormView(string $name, ?string $type = null): FormView
    {
        return $this->getForm($name, $type)->createView();
    }

    /**
     * Save the filtered values from form into session.
     * Possible default values for empty fields can be passed as third argument.
     *
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $options
     */
    public function saveFilter(string $type, string $name, array $defaults = [], array $options = []): bool
    {
        $this->forms[$name] = $this->formFactory->create($type, null, $options);
        if ($this->getRequest()->query->has('reset-filter')) {
            $this->getSession()->set('filter.'.$name, null);

            return true;
        }
        if (!empty($defaults) && null === $this->getSession()->get('filter.'.$name)) {
            $this->getSession()->set('filter.'.$name, $defaults);

            return true;
        }
        if (self::METHOD_QUERY === \strtoupper($this->forms[$name]->getConfig()->getMethod())) {
            return $this->saveQueryFilter($name);
        }
        if (!$this->getRequest()->query->has('submit-filter')) {
            return false;
        }
        $this->forms[$name]->handleRequest($this->getRequest());
        if ($this->forms[$name]->isSubmitted() && $this->forms[$name]->isValid()) {
            $this->getSession()->set('filter.'.$name, $this->getRequest()->query->all()[$this->forms[$name]->getName()]);

            return true;
        }

        return false;
    }

    /**
     * Values come from the request body, sent either with a real QUERY request
     * or with a POST request overridden by "_method" (what form_start() renders).
     * The form is submitted manually: Symfony < 7.4 doesn't know the QUERY method,
     * nor parses its body.
     */
    private function saveQueryFilter(string $name): bool
    {
        $request = $this->getRequest();
        $method = $request->getRealMethod();
        if (self::METHOD_QUERY !== $method && !('POST' === $method && self::METHOD_QUERY === \strtoupper((string) $request->request->get('_method')))) {
            return false;
        }
        $body = $request->request->all();
        if ([] === $body && '' !== ($content = $request->getContent())) {
            \parse_str($content, $body);
        }
        $formName = $this->forms[$name]->getName();
        if (!isset($body[$formName]) || !\is_array($body[$formName])) {
            return false;
        }
        /** @var array<string, mixed> $values */
        $values = $body[$formName];
        if ($this->forms[$name]->submit($values)->isValid()) {
            $this->getSession()->set('filter.'.$name, $values);

            return true;
        }

        return false;
    }

    /**
     * Save sorting information into session.
     */
    public function sort(string $name, string $field, string $direction = 'ASC'): void
    {
        $this->getSession()->set('filter_sort.'.$name, ['field' => $field, 'direction' => $direction]);
    }

    /**
     * @return FormInterface<string, string|FormInterface>
     */
    private function getForm(string $name, ?string $type = null): FormInterface
    {
        return $this->forms[$name] ?? $this->formFactory->create($type ?? FormType::class);
    }

    public function reset(): void
    {
        $this->forms = [];
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getMainRequest();
    }

    private function getSession(): SessionInterface
    {
        return $this->getRequest()->getSession();
    }

    /**
     * @return array<string, mixed>
     */
    private function getSort(string $name): array
    {
        $session = $this->getSession();
        if (!$session->has('filter_sort.'.$name)) {
            return [];
        }
        $sort = $session->get('filter_sort.'.$name);
        if (!\is_array($sort)) {
            $msg = \sprintf('filter_sort.%s should be an array, %s found', $name, \gettype($sort));
            throw new \UnexpectedValueException($msg);
        }

        return $sort;
    }
}
