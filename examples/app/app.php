<template>
    <Provider name="Parent attribute">
        <Post />
    </Provider>
</template>

<script>
document.body.style.backgroundColor = 'yellow';
</script>


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