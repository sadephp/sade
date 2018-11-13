<template>
    <Provider name="Parent attribute">
        <Post />
    </Provider>
</template>

<?php

return [
    'data' => function() {
        return [
            'greeting' => 'Hello'
        ];
    },
    'components' => [
        'Provider' => 'provider.php',
        'Post' => 'post.php',
    ]
];

?>