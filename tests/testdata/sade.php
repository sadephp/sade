<?php

use Sade\Sade;

return function (Sade $sade) {
    $sade->set('custom', true);
    $sade->set('mixins', [
        'methods' => [
            'hello' => function () {
                return 'Hello, world!';
            }
        ]
    ]);
};
