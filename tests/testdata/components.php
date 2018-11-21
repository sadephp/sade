<template>
    <span><Hello /></span>
</template>

<?php

return [
    'data' => function () {
        return [];
    },
    'components' => [
        'Hello' => 'hello.php',
    ]
];

?>
