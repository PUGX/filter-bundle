<?php

namespace PUGX\FilterBundle\Tests;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\Filter;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class FilterTest extends TestCase
{
    private Filter $filter;

    /** @var \PHPUnit\Framework\MockObject\MockObject&FormFactoryInterface */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
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
        // @phpstan-ignore-next-line method.notFound
        $this->factory->method('create')->with(StubFormType::class)->willReturn($form);
        $this->filter->saveFilter(StubFormType::class, 'foo');
        $data = $this->filter->getFormData('foo', 'bar');
        self::assertEquals('baz', $data);
    }

    public function testFormView(): void
    {
        $view = $this->createStub(FormView::class);
        $form = $this->createStub(FormInterface::class);
        $form->method('createView')->willReturn($view);
        // @phpstan-ignore-next-line method.notFound
        $this->factory->method('create')->with(StubFormType::class)->willReturn($form);
        $this->filter->saveFilter(StubFormType::class, 'foo');
        $formView = $this->filter->getFormView('foo');
        self::assertEquals($view, $formView);
    }

    public function testFormViewWithoutPreviousForm(): void
    {
        $view = $this->createStub(FormView::class);
        $form = $this->createStub(FormInterface::class);
        $form->method('createView')->willReturn($view);
        // @phpstan-ignore-next-line method.notFound
        $this->factory->method('create')->with(StubFormType::class)->willReturn($form);
        $formView = $this->filter->getFormView('foo', StubFormType::class);
        self::assertEquals($view, $formView);
    }

    public function testFormViewWhenSessionStartsDuringRequest(): void
    {
        // stored filter values, session not started yet: its id is empty until filter() reads it
        $storage = new MockArraySessionStorage();
        $storage->setSessionData(['_sf2_attributes' => ['filter.foo' => ['bar' => 'baz']]]);
        $session = new Session($storage);
        $request = Request::create('/');
        $request->setSession($session);
        $stack = new RequestStack();
        $stack->push($request);

        $view = $this->createStub(FormView::class);
        $form = $this->createStub(FormInterface::class);
        $form->method('createView')->willReturn($view);
        $form->method('submit')->willReturnSelf();
        $form->method('isValid')->willReturn(true);
        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects($this->once())->method('create')->with(StubFormType::class)->willReturn($form);
        $filter = new Filter($factory, $stack);

        self::assertSame('', $session->getId());
        $filter->saveFilter(StubFormType::class, 'foo');
        $filter->filter('foo'); // starts the session
        self::assertSame($view, $filter->getFormView('foo'), 'same form, even if the session id changed');
    }

    public function testReset(): void
    {
        $types = [];
        $form = $this->createStub(FormInterface::class);
        $form->method('createView')->willReturn($this->createStub(FormView::class));
        $this->factory->expects($this->exactly(2))->method('create')->willReturnCallback(static function (string $type) use (&$types, $form): FormInterface {
            $types[] = $type;

            return $form;
        });
        $this->filter->saveFilter(StubFormType::class, 'foo');
        $this->filter->reset();
        $this->filter->getFormView('foo');
        self::assertSame([StubFormType::class, FormType::class], $types, 'saved form is gone after reset');
    }

    public function testSort(): void
    {
        $this->filter->sort('foo', 'bar');
        $filter = $this->filter->filter('foo');
        self::assertArrayHasKey('_sort', $filter);
    }
}
