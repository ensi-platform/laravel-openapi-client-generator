<?php

return [

    /**
     * Path to the directory where index.yaml openapi file located
     */
    'apidoc_dir' => public_path('api-docs'),

    /**
     * Dir pattern where client package will be generated
     */
    'output_dir' => base_path('..' . PATH_SEPARATOR . 'test-client'),

    /**
     * Args for generate nodejs client
     */
    'nodejs_args' => [
        /**
         * Specific generator params from https://openapi-generator.tech/docs/generators/typescript-axios/
         */
        'params' => [
            'npmName' => '',
            'useES6' => true,
            'useSingleRequestParameter' => true,
            'withInterfaces' => true,
            'withSeparateModelsAndApi' => true,
            'typescriptThreePlus' => true,
            'apiPackage' => 'api',
            'modelPackage' => 'dto'
        ]
    ],

    /**
     * Args for generate php client
     */
    'php_args' => [
        'git_user_id' => '',

        'git_repo_id' => '',

        /**
         * Specific generator params from https://openapi-generator.tech/docs/generators/php/
         */
        'params' => [
            'apiPackage' => 'Api',
            'invokerPackage' => '',
            'modelPackage' => 'Dto',
            'packageName' => ''
        ]
    ]
];
