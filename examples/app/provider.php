<template>
    <div>
        <h5>Provider component</h5>
        {{ children | raw }}
    </div>
</template>

<script>
</script>

<style scoped>
    div {
        background: #d8e5e7;
        padding: 20px;
        margin: 20px 0;
    }
</style>

<?php

function withProvider(array $options)
{
    if (!isset($options['props'])) {
        $options['props'] = [];
    }

    $options['props'][] = 'name';

    return $options;
}

return [
    'data' => function () {
        return [];
    }
];
