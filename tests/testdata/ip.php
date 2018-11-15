<template>
    My IP address is {{ ip }}
</template>

<script>
    var ip = '{{ ip }}';
    console.log(ip);
</script>

<?php

return [
    'created' => function() {
        $this->ip = file_get_contents('https://api.ipify.org');
    }
];

?>