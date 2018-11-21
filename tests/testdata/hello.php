<template>
    <p>{{ greeting }}, world!</p>
</template>

<?php
return [
    'props' => [
        'greeting',
    ],
    'data'  => function () {
        return [
            'greeting' => 'Hello'
        ];
    }
];
?>
