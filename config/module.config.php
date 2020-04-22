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
                'type' => \Zend\Router\Http\Regex::class,
                'options' => [
                    'regex' => '/login(/.*)?',
                    'spec' => '/login',
                    'defaults' => [
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
//         'controllers' => [
//                 'factories' => [
//                         'RestrictedSites\Controller\Site\SiteLogin' => RestrictedSites\Service\Controller\Site\SiteLoginControllerFactory::class,
//                         'RestrictedSites\Stdlib\SiteMailer' => RestrictedSites\Service\SiteMailerFactory::class,
//                 ]
//         ],
//         'form_elements' => [
//             'factories' => [
//                 'RestrictedSites\Form\ConfigForm' => 'RestrictedSites\Service\Form\ConfigFormFactory',
//             ],
//         ],
//         'service_manager' => [
//             'factories' => [
//                 'RestricedSites\SiteMailer' => RestrictedSites\Service\SiteMailerFactory::class,
//                 'Omeka\Mailer' => RestrictedSites\Service\SiteMailerFactory::class, //required to customize user creation mail
//             ]
//         ],
//         'controller_plugins' => [
//             'factories' => [
//                 // Specific plugin in case I manage to NOT override core Mailer service:
//                 'sitemailer' => RestrictedSites\Service\ControllerPlugin\SiteMailerFactory::class,
//             ],
//         ],
//         'navigation_links' => [
//                 'invokables' => [
//                     'RestrictedSites\Site\Navigation\Link\Logout' => RestrictedSites\Site\Navigation\Link\Logout::class
//                     ],
//         ],
//         'translator' => [
//                 'translation_file_patterns' => [
//                         [
//                             'type' => 'gettext',
//                             'base_dir' => OMEKA_PATH . '/modules/RestrictedSites/language',
//                             'pattern' => '%s.mo',
//                             'text_domain' => null,
//                         ],
//                 ],
//         ],
//         'router' => [
//                 'routes' => [
//                         'sitelogin' => [
//                                 'type' => \Zend\Router\Http\Segment::class,
//                                 'options' => [
//                                         'route' => '/sitelogin/:site-slug',
//                                         'constraints' => [
//                                                 'site-slug' => '[a-zA-Z0-9_-]+'
//                                         ],
//                                         'defaults' => [
//                                                 '__NAMESPACE__' => 'RestrictedSites\Controller\Site',
//                                                 '__SITE__' => true,
//                                                 'controller' => 'SiteLogin',
//                                                 'action' => 'login'
//                                         ]
//                                 ],
//                                 'may_terminate' => true,
//                                 'child_routes' => [
//                                     'forgot-password' => [
//                                         'type' => \Zend\Router\Http\Segment::class,
//                                         'options' => [
//                                             'route' => '/forgot-password',
//                                             'defaults' => [
//                                                 'action' => 'forgot-password',
//                                             ],
//                                             'constraints' => [
//                                                 'controller' => 'SiteLogin',
//                                                 'action' => 'forgotPassword',
//                                             ],
//                                         ],
//                                     ],
//                                     'create-password' => [
//                                         'type' => \Zend\Router\Http\Segment::class,
//                                         'options' => [
//                                             'route' => '/create-password/:key',
//                                             'constraints' => [
//                                                 'key' => '[a-zA-Z0-9]+',
//                                             ],
//                                             'defaults' => [
//                                                 'action' => 'create-password',
//                                             ],
//                                             'constraints' => [
//                                                 'controller' => 'SiteLogin',
//                                                 'action' => 'createPassword',
//                                             ],
//                                         ],
//                                     ],
//                                 ],
//                         ],
//                         'sitelogout' => [
//                                 'type' => 'Segment',
//                                 'options' => [
//                                         'route' => '/sitelogout/:site-slug',
//                                         'constraints' => [
//                                             'site-slug' => '[a-zA-Z0-9_-]+'
//                                         ],
//                                         'defaults' => [
//                                                 '__NAMESPACE__' => 'RestrictedSites\Controller\Site',
//                                                 '__SITE__' => true,
//                                                 'controller' => 'SiteLogin',
//                                                 'action' => 'logout'
//                             ],
//                         ],
//                     ],
//                 ],
//         ],
];

