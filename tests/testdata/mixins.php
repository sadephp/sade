<template>
    <p>{{ hello() }}</p>
    <p>{{ created1 }}</p>
    <p>{{ created2 }}</p>
    <p>{{ method1() }}</p>
    <p>{{ method2() }}</p>
    <p>{{ method1()|filter1 }}</p>
    <p>{{ method2()|filter2 }}</p>
</template>

<?php

return [
    'created' => function () {
        $this->created2 = 'created2';
    },
    'filters' => [
        'filter2' => function () {
            return 'filter2';
        },
    ],
    'methods' => [
        'method2' => function () {
            return 'method2';
        },
    ],
];
