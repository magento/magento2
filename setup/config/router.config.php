<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

use Magento\Setup\Controller\ConsoleController;

return [
    'router' => [
        'routes' => [
            'literal' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Magento\Setup\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
            'setup' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '[/:controller[/:action]]',
                    'defaults' => [
                        '__NAMESPACE__' => 'Magento\Setup\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                ],
            ],
        ],
    ],
    'console' => ['router' => ['routes' => ConsoleController::getRouterConfig()]],
];
