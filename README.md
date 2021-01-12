PUGX FilterBundle
=================

[![Total Downloads](https://poser.pugx.org/pugx/filter-bundle/downloads.png)](https://packagist.org/packages/pugx/filter-bundle)
[![Build Status](https://github.com/PUGX/filter-bundle/workflows/Build/badge.svg)](https://github.com/PUGX/filter-bundle/actions)

The purpose of this bundle is providing a way to get some filters, that stay in session, to
be able to use them when displaying a list of items. It also supports sorting.

* [Setup](#Setup)
* [Basic Usage](#basic-usage)
* [Form Example](#form-example)
* [Sorting](#sorting)
* [Translation](#translation)
* [JavaScript](#javascript)
* [Helpers](#helpers)

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
            'form' => $filter->getFormView('foo'),
            'foos' => $foos,
        ]);
    }

}
```


```twig
{# this will display your form. Use '_pugx_filter_b4.html.twig' for Bootstrap 4 #} 
{% include '_pugx_filter.html.twig' with {name: 'foo'} %}

{% for foo in foos %}
    {# here you can display your list of filtered items, as long as you did your homework #}
{% endfor %}
```

Form Example
------------

```php
<?php

class MyFilterType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['required' => false])
            ->add('email', Type\TextType::class, ['required' => false])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
        ]);
    }
}

```

Sorting
-------

You can use provided Twig extension for column sorting functionality.

Example of template:

```twig
{# Use '_pugx_sort_b4.html.twig' for Bootstrap 4 #} 
{% from '_pugx_sort.html.twig' import sort -%}

{% block body %}
    {% include '_pugx_filter.html.twig' with {name: 'coach'} %}
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Name {{ sort('my_sorting_route', 'foo', 'name') }} </th>
            </tr>
        </thead>
        <tbody>
            {% for foo in foos %}
                {# same as above... #}
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
```

You need to provide a route/action to perform sorting, using `$filter->sort('foo', $field, $direction)`.

Then, you'll find an addtional value inside your filter array, like this:

```php
$filters = [
    '_sort' => [
        'field' => 'name',
        'direction' => 'ASC',
    ]
];
```

You can use this value to perform your sorting (again, this is up to you and it depends on your domain logic).


Translation
-----------

Translations are available (for now, only for English/French/Italian).

If you're using Symfony 4.4+, translatons should be automatically discovered.

On older Symfony versions, add this to your configuration:

```yaml
# config/packages/translation.yaml
framework:
    translator:
        paths:
            - '%kernel.project_dir%/translations/'  # this line should be already present
            - '%kernel.project_dir%/vendor/pugx/filter-bundle/translations/' # add this line

```

JavaScript
----------

A jQuery helper is provided, to enhance UX.
You can use it by requiring the following line in your package.json file:

```json
{
    "dependencies": {
        "@pugx/filter-bundle": "file:vendor/pugx/filter-bundle/assets"
    }
}
```

Then you can do something like the following:

```js
// assets/js/app.js
import '@pugx/filter-bundle/js/filter';

$(document).ready(function () {
    'use strict';

    if (jQuery().pugxFilter) {
        $('#filter').pugxFilter();
    }
});

```

The basic results will be that icon in the toggle button will be toggled along,
and the arrow will point to right when filters are shown (and back to bottom when
filters are collapsed).

You can pass an option object to `pugxFilter` function.

Currently supported options are:

* `callbackHide` a callback to be used when filters are collapsed
* `callbackShow` a callback to be used when filters are shown

Helpers
-------

Following helper functions are available as functions in twig templates:

* `filter_has`: tells if a filter is enabled (e.g. if a session for filter exists)
* `filter_is`: tells if a filter field is selected
* `filter_is_not`: tells if a filter field is not selected

