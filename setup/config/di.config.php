<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'di' => [
        'allowed_controllers' => [
            'Magento\Setup\Controller\Index',
            'Magento\Setup\Controller\Landing',
            'Magento\Setup\Controller\Navigation',
            'Magento\Setup\Controller\License',
            'Magento\Setup\Controller\ReadinessCheck',
            'Magento\Setup\Controller\Environment',
            'Magento\Setup\Controller\DatabaseCheck',
            'Magento\Setup\Controller\AddDatabase',
            'Magento\Setup\Controller\WebConfiguration',
            'Magento\Setup\Controller\CustomizeYourStore',
            'Magento\Setup\Controller\CreateAdminAccount',
            'Magento\Setup\Controller\Install',
            'Magento\Setup\Controller\Success',
            'Magento\Setup\Controller\Modules',
        ],
        'instance' => [
            'preference' => [
                'Zend\EventManager\EventManagerInterface' => 'EventManager',
                'Zend\ServiceManager\ServiceLocatorInterface' => 'ServiceManager',
                'Magento\Framework\DB\LoggerInterface' => 'Magento\Framework\DB\Logger\Quiet',
                'Magento\Framework\Locale\ConfigInterface' => 'Magento\Framework\Locale\Config',
                'Magento\Framework\Filesystem\DriverInterface' => 'Magento\Framework\Filesystem\Driver\File',
                'Magento\Framework\Component\ComponentRegistrarInterface' =>
                    'Magento\Framework\Component\ComponentRegistrar',
            ],
        ],
    ],
];
