<template>
    <h5>App Component</h5>
    <Provider name="App Name">
        <Post />
    </Provider>
</template>

<style scoped>
    {
        font-family: Helvetica,Arial,sans-serif;
    }

    p {
        background: #232323;
        padding: 10px;
    }
</style>

<?php

return [
    'data' => function () {
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
