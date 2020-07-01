<?php
return [
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity'
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies'
        ]
    ],
    'service_manager' => [
        'factories' => [
            // Overriding Core
            'Omeka\AuthenticationService' => UserNames\Service\AuthenticationServiceFactory::class
        ]
    ],
    'controllers' => [
      'factories' => [
          'UserNames\Controller\Login' => UserNames\Service\Controller\LoginControllerFactory::class,
      ],
    ],
    'router' => [
        'routes' => [
            'login' => [
                'type' => \Laminas\Router\Http\Regex::class,
                'options' => [
                    'regex' => '/login(/.*)?',
                    'spec' => '/login',
                    'defaults' => [
                        // Overriding Core
                        'controller' => 'UserNames\Controller\Login',
                        'action' => 'login',
                    ],
                ],
            ],
            ],
        ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/UserNames/language',
                'pattern' => '%s.mo',
                'text_domain' => null
            ]
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/UserNames/view'
        ]
    ],
    'api_adapters' => [
        'invokables' => [
            'usernames' => UserNames\Api\Adapter\UserNameAdapter::class
        ]
    ],
    'form_elements' => [
        'factories' => [
            'UserNames\Form\ConfigForm' => 'UserNames\Service\Form\ConfigFormFactory'
        ]
    ],
];

