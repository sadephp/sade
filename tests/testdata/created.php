<template>
    <p>{{ greeting }}, world!</p>
</template>

<?php
return [
    'created' => function () {
        $this->greeting = 'Created';
    },
    'data' => function () {
        return [
            'greeting' => 'Hello'
        ];
    }
];
?>
