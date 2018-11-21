<template>
    <div>
        <h5>Post component</h5>
        <h3>Provider prop: {{ name }}</h3>
        <p>post.php</p>
    </div>
</template>

<style scoped>
    div {
        background: #4a6f7d;
        color: white;
    }
</style>

<?php

return withProvider([]);

?>
