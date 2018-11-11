<template>
    <p>
        <img src="{{ image }}" />
    </p>
</template>

<script>
    return [
        'data' => function() {
            return [
                'image' => 'https://php.net/images/logos/php-logo.svg'
            ];
        }
    ];
</script>

<style>
    img {
        width: 200px;
    }
</style>