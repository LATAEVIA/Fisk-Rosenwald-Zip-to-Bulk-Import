<?php declare(strict_types=1);
namespace ZipImporter;

return [
    'media_ingesters' => [
        'factories' => [
            'tempfile' => Service\MediaIngesterTempFileFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view'
        ],
    ],
    'controllers' => [
        'factories' => [
            'ZipImporter\Controller\Index' => Service\Controller\IndexControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'zip-importer' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/zip-importer',
                            'defaults' => [
                                '__NAMESPACE__' => 'ZipImporter\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'upload' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/upload',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ZipImporter\Controller',
                                        'controller' => 'Index',
                                        'action' => 'upload',
                                    ],
                                ],
                            ],
                            'mapping' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/mapping',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ZipImporter\Controller',
                                        'controller' => 'Index',
                                        'action' => 'mapping',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Zip Import',
                'route' => 'admin/zip-importer',
                'resource' => 'ZipImporter\Controller\Index'
            ],
        ],
    ]
];