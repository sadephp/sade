<template>
    <p>{{ greeting }} World!</p>
    <image />
    <form />
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