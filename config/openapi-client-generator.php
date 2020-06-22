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
            'npmName' => 'open-api-example-client-ts',
            'useES6' => true,
            'useSingleRequestParameter' => true,
            'withInterfaces' => true,
            'withSeparateModelsAndApi' => true,
            'typescriptThreePlus' => true,
            'apiPackage' => 'api',
            'modelPackage' => 'dto'
        ]
    ]
];
