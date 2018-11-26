<?php

use Sade\Sade;

return function (Sade $sade) {
    $sade->set('custom', true);
    $sade->set('mixins', [
        [
            'created' => function () {
                $this->created1 = 'created1';
            },
            'filters' => [
                'filter1' => function () {
                    return 'filter1';
                },
            ],
            'methods' => [
                'hello' => function () {
                    return 'Hello, world!';
                },
                'method1' => function () {
                    return 'method1';
                },
            ]
        ],
    ]);
};
