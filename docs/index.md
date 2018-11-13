# Sade Docs

Sade, a library for creating PHP Components with [Twig](https://twig.symfony.com/doc/2.x/) (2.x). This package will not do any preprocessing or somthing like that.

* [Sade Options](#sade-options)
* [Render method](#render-method)
* [Sade CLI](#sade-cli)
* [Components](#components)
* - [Template tag](#template-tag)
* - [Script tag](#script-tag)
* - [Style tag](#style-tag)
* - [PHP data](#php-data)
* - [Parent component](#parent-component)
* - [Children prop](#children-prop)

## Sade Options

Default options:

```php
[
    'cache'    => [
        'dir'  => '',
        'perm' => ( 0755 & ~ umask() ),
    ],
    'style'    => [
        'scoped' => false
    ],
    'template' => [
        'scoped' => false
    ],
]
```

* `cahce.dir` cache directory path.
* `style.scoped` will force scoped CSS and html if set to true.
* `template.scoped` will force only scoped html if set to true.

```php
$sade = new \Sade\Sade( __DIR__ . '/path/to/components', $options );
```

## Render method

To render a Sade component you create a new instanceof the `\Sade\Sade` class with the source directory where you're components exists. If no directory is given then `getcwd()` will be used.

Example:

```php
$sade = new \Sade\Sade( __DIR__ . '/path/to/components' );

echo $sade->render('greeting.php');
```

## Sade CLI 

Sade provides a simple CLI to render components through the terminal.

```
vendor/bin/sade path/to/component.php > component.html
```

## Components

Sade components takes it insperation from other components packages so you may be familiar. A component can have a template, script, style and php data.

Example:

```php
<template>
    <p>{{ greeting }} World!</p>
</template>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>

<?php
    return [
        'data' => function() {
            return [
                'greeting' => 'Hello'
            ];
        }
    ];
?>
```

Template, script and style tags has a `src` attribute that can include other files instead of having all in the same file. All other attributes (except `src` attribute and some special ones) will be passed along to div (when scoped), script and style tags, some additional attributes will be added, read more about this below under each tag.

Example:

```html
<template src="accordion.twig" />
<script src="accordion.js" />
<style src="accordion.css" />
```

### Template tag

The template tag contains [Twig](https://twig.symfony.com/doc/2.x/) (2.x) code and are compiled to HTML at runtime and cached if configured.

All attributes will be passed along when template is scoped either via `template.scoped` or `style.scoped` or when a style tag has `scoped` attribute.

### Script tag

All attributes will be passed along and `data-sade-id` will be added with the div id when scoped.

### Style tag

All attributes will be passed along except the `scoped` attribute.

The style tag can scope CSS with a uniq ID that is added to a div tag.

Example:

```html
<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>
```

Output:

```html
<style type="text/css">
#sade-l3uvc2l0zxmv p {
    font-size: 2em;
    text-align: center;
}
</style>
```

### PHP data

PHP data use regulare `<?php ?>` tags. The returned array look like this:

```php
<?php
    return [
        'components' => [
            'image.php', // <image />
            'Parent' => 'parent.php' // <Parent />
        ],
        'filters'    => [
            // Twig filters.
        ],
        'data'       => function() {
            return [
                // Data to component.
            ];
        },
        'methods'    => [
            // Twig functions.
        ],
        'props'      => [
            // Requested props from parent component.
        ]
    ];
?>
```

* `components` are a array with key/value of other components to use.
* `filters` are a key/function array for Twig filters. You can access data here `$this->greeting` equals `greetings` from data array.
* `data` are a function that returns a array or array of data.
* `methods` are a key/function array for Twig functions. You can access data here `$this->greeting` equals `greetings` from data array.
* `props` are a array of properties to request from the parent component. Only parent properties is needed to be listed, not properties on a component html tag.

### Parent components

Example using a parent component with properties:

```html
<template>
    <Parent name="Sade">
        <Child />
    </Parent>
</template>
```

Example of the child component:

```php
<template>
    {{ name }}
</template>

<?php
    return [
        'props' => [
            'name'
        ]
    ];
?>
```

### Children prop

We inject a `children` variable into Twig so you can render children html of a component html tag.

Example of parent and child component html tag:

```html
<template>
    <Parent name="Sade">
        <Child />
    </Parent>
</template>
```

Example of parent component:

```html
<template>
    {{ children }}
</template>
```