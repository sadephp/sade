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
        $this->ip = $this->http('https://api.ipify.org');
    },
    'data' => [
        'ip' => 'Missing'
    ]
];

?>