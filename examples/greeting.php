<template src="greeting.twig" />

<?php
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
?>

<script>
    document.body.style.backgroundColor = 'red';
</script>

<style scoped>
    p {
        font-size: 2em;
        text-align: center;
    }
</style>