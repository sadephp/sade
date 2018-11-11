# Rain

> Should not be used in production

Just a prototype of PHP components that looks like Vue components.

## Example

```vue
<template>
    <p>{{ greeting }} World!</p>
    <image props-alt="{{ greeting }}" />
</template>

<script>
    return [
        'data' => function() {
            return [
                'greeting' => 'Hello'
            ];
        },
        'components' => [
            'image.php'
        ]
    ];
</script>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>
```

To render

```php
$rain = new \Frozzare\Rain\Rain( [
    'dir'   => __DIR__ . '/examples',
    'style' => [
        // Force scoped style.
        'scoped' => true
    ]
] );

echo $rain->render( 'greeting.php' );
```

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)