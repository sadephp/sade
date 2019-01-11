<template>
    My IP address is {{ ip }}
</template>

<script>
    var ip = '{{ ip }}';
    console.log(ip);
    // hello world
</script>

<?php

$this->set('http', function () {
    return new class {
        public function get($url) {
            return file_get_contents($url);
        }
    };
});

return [
    'created' => function () {
        $this->ip = $this->http()->get('https://api.ipify.org');
    },
    'data' => [
        'ip' => 'Missing'
    ],
];

?>
