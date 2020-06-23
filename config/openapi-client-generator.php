<?php

return [

    /**
     * Path to the directory where index.yaml openapi file located
     */
    'apidoc_dir' => public_path('api-docs'),

    /**
     * Dir template where client package will be generated
     */
    'output_dir_template' => base_path('..' . PATH_SEPARATOR . '<paste_your_client_package_name>'),

    /**
     * Git user
     */
    'git_user' => '<paste_your_git_user>',

    /**
     * Git repository name template
     */
    'git_repo_template' => '<paste_your_git_repo_template>',

    /**
     * Git host
     */
    'git_host' => 'gitlab.com',

    /**
     * Args for generate nodejs client
     */
    'js_args' => [
        /**
         * Specific generator params from https://openapi-generator.tech/docs/generators/typescript-axios/
         */
        'params' => [
            'npmName' => '<paste_your_npm_package_name>',
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
        /**
         * Specific generator params from https://openapi-generator.tech/docs/generators/php/
         */
        'params' => [
            'apiPackage' => 'Api',
            'invokerPackage' => '<paste_your_php_package_namespace>',
            'modelPackage' => 'Dto',
            'packageName' => '<paste_your_php_package_name>'
        ]
    ]
];
