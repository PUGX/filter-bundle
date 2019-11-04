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

You need to provide a route/action to perform sorting, using `$filter->sort('utente', $field, $direction)`.

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

On older Symfonyi versions, add this to your configuration:

```yaml
# config/packages/translation.yaml
framework:
    translator:
        paths:
            - '%kernel.project_dir%/translations/'  # this line should be already present
            - '%kernel.project_dir%/vendor/pugx/filter-bundle/translations/' # add this line

```
