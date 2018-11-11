<template>
    <p>{{ greeting }} World!</p>
</template>

<?php
    return [
        'data' => function() {
            return [
                'greeting' => 'Hello'
            ];
        }
    ];
?>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>