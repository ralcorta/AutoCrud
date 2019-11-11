<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration for scaffolding
    |--------------------------------------------------------------------------
    |
    | Here you can define models and attrbutes which the package take to
    | generate scaffolding.
    |
    | Structure: 'Model' => [
    |               'attriuteOne' => 'validations|and|more|validations',
    |               'attriuteTwo' => ['validations','and','more','validations']
    |            ]
    |
    */

    'Example' => [
        'attributes' => [
            'attributeOne' => [
                'type' => 'integer',
                'rules' => 'numeric|required',
                'default' => 'value'
            ],
            'attributeTwo' => [
                'type' => 'string',
                'rules' => 'string|nullable',
                'required' => false,
            ]
        ],
        'relationships' => [
            'belongsTo' => 'Example2',
            'hasMany' => 'Example3'
        ],
        'executed' => false // True if you don't want to execute this model scaffolding. False is by default
    ]

];
