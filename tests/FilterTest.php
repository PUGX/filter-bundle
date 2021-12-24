<?php

namespace PUGX\FilterBundle\Tests;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\Filter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class FilterTest extends TestCase
{
    private Filter $filter;

    /** @var \PHPUnit\Framework\MockObject\MockObject|FormFactory */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactory::class);
        $fakeRequest = Request::create('/');
        $fakeRequest->setSession(new Session(new MockArraySessionStorage()));
        $stack = new RequestStack();
        $stack->push($fakeRequest);
        $this->filter = new Filter($this->factory, $stack);
    }

    public function testFilter(): void
    {
        $this->filter->saveFilter(StubFormType::class, 'foo');
        $filter = $this->filter->filter('foo');
        self::assertEquals([], $filter);
    }

    public function testFormData(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('getData')->willReturn(['bar' => 'baz']);
        $this->factory->method('create')->with(StubFormType::class)->willReturn($form);
        $this->filter->saveFilter(StubFormType::class, 'foo');
        $data = $this->filter->getFormData('foo', 'bar');
        self::assertEquals('baz', $data);
    }

    public function testFormView(): void
    {
        $view = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($view);
        $this->factory->method('create')->with(StubFormType::class)->willReturn($form);
        $this->filter->saveFilter(StubFormType::class, 'foo');
        $formView = $this->filter->getFormView('foo');
        self::assertEquals($view, $formView);
    }

    public function testFormViewWithoutPreviousForm(): void
    {
        $view = $this->createMock(FormView::class);
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn($view);
        $this->factory->method('create')->with(StubFormType::class)->willReturn($form);
        $formView = $this->filter->getFormView('foo', StubFormType::class);
        self::assertEquals($view, $formView);
    }

    public function testSort(): void
    {
        $this->filter->sort('foo', 'bar');
        $filter = $this->filter->filter('foo');
        self::assertArrayHasKey('_sort', $filter);
    }
}
