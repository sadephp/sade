# Sade

[![Build Status](https://travis-ci.org/sadephp/sade.svg?branch=master)](https://travis-ci.org/sadephp/sade)

> Work in progress!

Sade, a library for creating PHP Components with [Twig](https://twig.symfony.com/doc/2.x/) (2.x). This package will not do any preprocessing or somthing like that.

## Documentation

The documentation is located [here](https://github.com/sadephp/sade/blob/master/docs/index.md)

## Example

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

To render

```php
$sade = new \Sade\Sade(__DIR__ . '/examples');

echo $sade->render('greeting.php');
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)