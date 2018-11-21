<template>
    {% if name %}
        <p>{{ greeting }} {{ name }}!</p>
    {% endif %}

    <form method="post">
        <p>
            <input type="text" name="name" />
        </p>
        <p>
            <button>{{ submit }}</button>
        </p>
    </form>
</template>

<?php
    return [
        'data' => function () {
            return [
                'name' => $_POST['name'] ?? '',
                'greeting' => 'Hi',
                'submit' => 'Submit',
            ];
        }
    ];
    ?>
