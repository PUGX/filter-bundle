<?php

namespace PUGX\FilterBundle;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class Filter
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RequestStack */
    private $requestStack;

    /** @var array|FormInterface[] */
    private $forms;

    public function __construct(FormFactoryInterface $formFactory, RequestStack $requestStack)
    {
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->forms = [];
    }

    /**
     * Perform actual filtering. You need to pass ad identifying name.
     * You'll get an array with name of fields in keys and filtered values in values
     * (except for "_sort" key, that holds info for sorting).
     *
     * @return array<string, mixed>
     */
    public function filter(string $name): array
    {
        $filter = [];
        $fname = $name.$this->getSession()->getId();
        $values = $this->getSession()->get('filter.'.$name);
        if (null !== $values) {
            if ($this->forms[$fname]->isSubmitted() || $this->forms[$fname]->submit($values)->isValid()) {
                $filter = \array_filter($values, static function ($value): bool {
                    return '' !== $value;
                });
            }
        }
        if ([] !== ($sort = $this->getSort($name))) {
            $filter['_sort'] = $sort;
        }

        return $filter;
    }

    /**
     * Get value of a single form field.
     *
     * @return mixed
     */
    public function getFormData(string $name, string $field)
    {
        return $this->getForm($name)->getData()[$field];
    }

    /**
     * Get the form object to pass to a template.
     */
    public function getFormView(string $name): FormView
    {
        return $this->getForm($name)->createView();
    }

    /**
     * Save filtered values from form into session.
     * Possible default values for empty fields can be passed as last argument.
     *
     * @param array<string, mixed> $defaults
     */
    public function saveFilter(string $formName, string $name, array $defaults = []): bool
    {
        $fname = $name.$this->getSession()->getId();
        $this->forms[$fname] = $this->formFactory->create($formName);
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

    private function getForm(string $name): FormInterface
    {
        $name .= $this->getSession()->getId();

        return $this->forms[$name] ?? $this->formFactory->create($name);
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

        return $session->has('filter_sort.'.$name) ? $session->get('filter_sort.'.$name) : [];
    }
}
