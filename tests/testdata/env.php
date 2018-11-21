<template>
    <p>{{ env('greeting') }}, world!</p>
</template>

<?php
return [
    'created' => function () {
        putenv('greeting=Hello');
    },
];
?>
