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
    // Change the config file name.
    'config'   => [
        'file' => 'sade.php',
    ],
    // Will cache output html so if you render same component again with only
    // the html and not script and style tags again.
    'cache'    => true,
    'script'   => [
        // Point to your own script generator class.
        'class'   => \Sade\Component\Script::class,
        // Enable script tag rendering.
        'enabled' => true,
        // Scope script and template.
        'scoped'  => false,
    ],
    'style'    => [
        // Point to your own style generator class.
        'class'   => \Sade\Component\Style::class,
        // Enable style tag rendering.
        'enabled' => true,
        'scoped'  => false,
        // Scope style, script and template.
        'tag'     => 'script',
    ],
    'template' => [
        // Point to your own template generator class.
        'class'   => \Sade\Component\Template::class,
        // Enable template tag rendering.
        'enabled' => true,
        // Scope script and template.
        'scoped'  => false,
    ],
]

$sade = new \Sade\Sade(__DIR__ . '/path/to/components', $options);
```

## Configuration files

When using the CLI or the [boilerplate](https://github.com/sadephp/boilerplate) you can configure Sade with a configuration file. When Sade is created it will look for `sade.php` in the directory passed and load it. If a function is returned it will send in the Sade instance as a argument.

Example config file:

```php
<?php

use Sade\Sade;

return function(Sade $sade) {
    // custom configuration.

};
```

## Render method

To render a Sade component you create a new instanceof the `\Sade\Sade` class with the source directory where you're components exists. If no directory is given then `getcwd()` will be used.

Example:

```php
$sade = new \Sade\Sade(__DIR__ . '/path/to/components');

echo $sade->render('greeting.php');
```

You can also render an array of files:

```php
echo $sade->render(['greeting.php', 'greeting.php']);
```

To only render one of the type tags (template, script or style):

```php
echo $sade->only('script')->render('greeting.php');

// or call magic methods (template, script or style):
echo $sade->script('greeting.php');
```

## Sade CLI

Sade provides a simple CLI to render components through the terminal.

Example:

```
vendor/bin/sade --src "components/**/index.php" --out build
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

Template, script and style tags has a `src` attribute that can include other files instead of having all in the same file. All other attributes (except scoped and some special ones) will be passed along to div (when scoped), script and style tags, some additional attributes will be added, read more about this below under each tag.

Example:

```html
<template src="accordion.twig" />
<script src="accordion.js" />
<style src="accordion.css" />
```

If combining `src` with a `external` attribute, then `src` attribute will be passed along as a real `src` attribute and no inline content will be added.

```html
<!-- Inline -->
<template src="accordion.twig" />
<!-- Not Inline -->
<script src="accordion.js" external />
<!-- Not inline -->
<style src="accordion.css" external />
```

### Template tag

The template tag contains [Twig](https://twig.symfony.com/doc/2.x/) (2.x) code and are compiled to HTML at runtime and cached if configured.

All attributes will be passed along and a class will be added when template is:
- scoped attribute on template tag or `template.scoped` option
- scoped attrbiute on script tag or `script.scoped` option
- scoped attrbitue on style tag or `style.scoped` option

### Script tag

All attributes will be passed along and `data-sade-class` will be added when script is:
- scoped attribute on template tag or `template.scoped` option
- scoped attrbiute on script tag or `script.scoped` option
- scoped attrbitue on style tag or `style.scoped` option

### Style tag

All attributes will be passed along except scoped attribute. `data-sade-class` will be added with the div class name when scoped. The style tag will scope CSS with a uniq class name when scoped. Only scoped options from style tag will scope the CSS and not template or script scoped options.

The style tag will be rendered by default but can be configured to be rendered as a style tag.

Example:

```html
<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>
```

CSS Output:

```css
.sade-8lhpfz p {
    font-size: 2em;
    text-align: center;
}
```

To style scoped div:

```html
<style scoped>
    {
        font-size: 2em;
        text-align: center;
    }
</style>
```

CSS Output:

```css
.sade-8lhpfz {
    font-size: 2em;
    text-align: center;
}
```

### PHP data

PHP data use regulare `<?php ?>` tags. The returned array look like this:

```php
<?php
    return [
        'created'    => function() {
            // Update data with new values when component is being rendered.
            // Example: $this->ip = file_get_contents('https://api.ipify.org');
        },
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
            // Request props and data from parent component.
            // Request data from top parent component.
        ]
    ];
?>
```

* `components` are a array with key/value of other components to use.
* `filters` are a key/function array for Twig filters. You can access data here `$this->greeting` equals `greetings` from data array.
* `data` are a function that returns a array or array of data.
* `methods` are a key/function array for Twig functions. You can access data here `$this->greeting` equals `greetings` from data array.
* `props` are a array of properties to request from the parent component. Only parent properties is needed to be listed, not properties on a component html tag.

### Custom functions

You can add custom functions or create plugins and bind them with:

```php
$sade->bind('http', function($sade) {
    return function ($url) {
        return file_get_contents($url);
    };
});
```

Then you can call them in `created, filters and methods` functions.

```php
return [
    'created' => function() {
        $this->ip = $this->http('https://api.ipify.org');
    }
];
```

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

### Inherit functions

Instead of a child component add property names to `props` array they can use a inherit function. The inherit function is a custom function you write.

```php
function withParent(array $options) {
    if (!isset($options['props'])) {
        $options['props'] = [];
    }
     $options['props'][] = 'name';
     return $options;
}
```

Example of child component options:

```php
return withParent([]);
```