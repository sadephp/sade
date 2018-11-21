<template>
    <Parent>
        <p>{{ greeting }}, world!</p>
    </Parent>
</template>

<?php

return [
    'components' => [
        'Parent' => 'parent.php',
    ],
    'data' => function () {
        return [
            'greeting' => 'Hello',
        ];
    },
];
