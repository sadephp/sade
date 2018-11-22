<template>
    <p>{{ greeting }}, world!</p>
</template>

<?php
return [
    'created' => function () {
        $this->greeting = $this->created();
    },
    'data' => function () {
        return [
            'greeting' => 'Hello'
        ];
    },
    'methods' => [
        'created' => function () {
            return 'Created';
        },
    ],
];
?>
