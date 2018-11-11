<template>
    <p>{{ greeting }} World!</p>
    <image />
    <form greeting="{{ greeting }}" />
    <p>UniqID ID is {{ uniqid() }}</p>
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
        ],
        'methods' => [
            'uniqid' => function() {
                return uniqid();
            }
        ]
    ];
</script>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>