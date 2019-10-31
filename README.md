PUGX FilterBundle
=================

The purpose of this bundle is providing a way to get some filters, that stay in session, to
be able to use them when displaying a list of items. It also supports sorting.

Setup
-----

Run `composer require pugx/filter-bundle`. No configuration is required.

Basic Usage
-----------

Inject provided service in your controller and use it with a form.

Your form should use `GET` as method, use some fields that make sense on your list of item,
and **not** use CSRF protection.

First step is to save filter with a name (if form is submitted). Then, you can get
a key/value array in `$filter->filter('foo')`, where "foo" is the name you provided above.

Using such array to retrieve filtered value is up to you: this bundle makes no assumptions on
your domain and doesn't do magic.

Here is an example:

```php
<?php

class FooController extends AbstractController
{
    public function itemList(Repository $repository, Filter $filter): Response
    {
        if ($filter->saveFilter(Form\FooFilterType::class, 'foo')) {
            return $this->redirectToRoute('foo_item_list');
        }
        // this is just an example: please implement your own method
        $foos = $repository->getList($filter->filter('foo'));

        return $this->render('foo/item_list.html.twig', [
            'form' => $filter->getFormView('utente'),
            'foos' => $foos,
        ]);
    }

}
```


```twig
{# this will display your form #} 
{% include '_pugx_filter.html.twig' with {name: 'foo'} %}

{% for foo in foos %}
    {# here you can display your list of filtered items, as long as you did your homework #}
{% endfor %}

```

Form Example
------------

This part of documentation still needs to be covered... TODO

Sorting
-------

This part of documentation still needs to be covered... TODO
