<?php

return [

    /**
     * Path to the directory where index.yaml openapi file located
     */
    'apidoc_dir' => public_path('api-docs'),

    /**
     * Dir template where client package will be generated
     */
    'output_dir_template' => base_path('..' . DIRECTORY_SEPARATOR . '<paste_your_client_package_name>'),

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
     * Args for generate php client
     */
    'php_args' => [
        /**
         * Package name for composer, use standard pattern namespace/package
         */
        'composer_name' => 'paste_your_composer_package_name',

        /**
         * Specific generator params from https://openapi-generator.tech/docs/generators/php/
         */
        'params' => [
            'apiPackage' => 'Api',
            'invokerPackage' => '<paste_your_php_package_namespace>',
            'modelPackage' => 'Dto',
            'packageName' => '<paste_your_php_package_name>',
        ],

        /**
         * Directory where you can place templates to override default ones. . Used in -t
         */
        'template_dir' => '',

        /**
         * Files that will be ignored during repository cleanup
         */
        'files_to_ignore_during_cleanup' => ['.git', '.gitignore'],

        /**
         * Options for disable patch section "require" composer.json
         */
        'composer_disable_patch_require' => false,

        /**
         * Options for disable patch enum
         */
        'enum_disable_patch' => false,
    ],

    'async_api' => [
        'enabled' => true,
        'doc_dir' => public_path('async-docs/v1'),
        'output_dir' => "lib" . DIRECTORY_SEPARATOR . "Kafka",
        'package' => "Ensi\ServiceClient\Kafka",
        'template_dir' => base_path('..' . DIRECTORY_SEPARATOR . '<paste_your_client_package_name>'),
    ],
];
