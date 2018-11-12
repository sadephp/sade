<template>
    <p>{{ greeting }}, world!</p>
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