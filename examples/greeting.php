<template>
    <p>{{ greeting }} World!</p>
    <image></image>
    <form props-greeting="{{ greeting }}" />
</template>

<script>
    return [
        'data' => function() {
            return [
                'greeting' => 'Hello'
            ];
        },
        'components' => [
            'image' => 'image.php',
            'form.php',
        ]
    ];
</script>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>