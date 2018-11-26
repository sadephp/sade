<template>
    <p>{{ hello() }}</p>
    <p>{{ created }}</p>
    <p>{{ created2 }}</p>
</template>

<?php

return [
    'created' => function () {
        $this->created2 = 'created';
    },
];
