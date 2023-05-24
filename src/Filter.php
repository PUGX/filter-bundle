<?php

namespace PUGX\FilterBundle;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class Filter
{
    /** @var array<int, FormInterface> */
    private array $forms;

    public function __construct(private FormFactoryInterface $formFactory, private RequestStack $requestStack)
    {
        $this->forms = [];
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
        $fname = $name.$this->getSession()->getId();
        /** @var array<string, mixed> $values */
        $values = $this->getSession()->get('filter.'.$name);
        if (null !== $values) {
            if ($this->forms[$fname]->isSubmitted() || $this->forms[$fname]->submit($values)->isValid()) {
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
        $fname = $name.$this->getSession()->getId();
        $this->forms[$fname] = $this->formFactory->create($type, null, $options);
        if ($this->getRequest()->query->has('reset-filter')) {
            $this->getSession()->set('filter.'.$name, null);

            return true;
        }
        if (!empty($defaults) && null === $this->getSession()->get('filter.'.$name)) {
            $this->getSession()->set('filter.'.$name, $defaults);

            return true;
        }
        if (!$this->getRequest()->query->has('submit-filter')) {
            return false;
        }
        $this->forms[$fname]->handleRequest($this->getRequest());
        if ($this->forms[$fname]->isSubmitted() && $this->forms[$fname]->isValid()) {
            $this->getSession()->set('filter.'.$name, $this->getRequest()->query->all()[$this->forms[$fname]->getName()]);

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
        $name .= $this->getSession()->getId();

        return $this->forms[$name] ?? $this->formFactory->create($type ?? FormType::class);
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
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
