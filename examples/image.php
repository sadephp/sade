<template>
    <p>
        <img src="{{ image }}" />
    </p>
</template>

<?php
    return [
        'data' => function () {
            return [
                'image' => 'https://php.net/images/logos/php-logo.svg'
            ];
        }
    ];
    ?>

<style>
    img {
        width: 200px;
    }
</style>