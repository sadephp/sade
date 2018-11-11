<template>
    {% if name %}
        <p>Hello {{ name }}!</p>
    {% endif %}

    <form method="post">
        <p>
            <input type="text" name="name" />
        </p>
        <p>
            <button>Submit</button>
        </p>
    </form>
</template>

<script>
    return [
        'data' => function() {
            return [
                'name' => $_POST['name'] ?? ''
            ];
        }
    ];
</script>