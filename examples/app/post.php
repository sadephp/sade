<template>
    <h3>From parent: {{ name }}</h3>
    <p>post.php</p>
</template>

<script>
document.body.style.backgroundColor = 'red';
</script>

<?php

return [
    'props' => [
        'name'
    ]
];

?>