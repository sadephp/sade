<template>
    My IP address is {{ ip }}
</template>

<script>
    var ip = '{{ ip }}';
    console.log(ip);
</script>

<?php

$this->set('http', function () {
    return new class {
        public function get($url) {
            return '1.1.1.1';
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
