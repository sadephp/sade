<?php

use Sade\Sade;

return function (Sade $sade) {
    $sade->set('custom', true);
    $sade->set('mixins', [
        [
            'created' => function () {
                $this->created = 'mixin created';
            },
            'methods' => [
                'hello' => function () {
                    return 'Hello, world!';
                }
            ]
        ],
    ]);
};
