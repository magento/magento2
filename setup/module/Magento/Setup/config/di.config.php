<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return [
    'di' => [
        'allowed_controllers' => [
            'Magento\Setup\Controller\ReadinessCheckController',
            'Magento\Setup\Controller\ReadinessCheck\ProgressController',
            'Magento\Setup\Controller\AddDatabaseController',
            'Magento\Setup\Controller\WebConfigurationController',
            'Magento\Setup\Controller\CustomizeYourStoreController',
            'Magento\Setup\Controller\CreateAdminAccountController',
            'Magento\Setup\Controller\SuccessController',
            'Magento\Setup\Controller\Success\EncryptionController',
            'Magento\Setup\Controller\InstallController',
            'Magento\Setup\Controller\Install\ProgressController',
            'Magento\Setup\Controller\Install\ClearProgressController',
            'Magento\Setup\Controller\Install\StartController',
            'Magento\Setup\Controller\IndexController',
            'Magento\Setup\Controller\LandingController',
            'Magento\Setup\Controller\EnvironmentController',
            'Magento\Setup\Controller\UserController',
            'Magento\Setup\Controller\ConsoleController',

            'Magento\Setup\Controller\Controls\HeaderController',
            'Magento\Setup\Controller\Controls\MenuController',
            'Magento\Setup\Controller\Controls\NavbarController',

            'Magento\Setup\Controller\Data\FilePermissionsController',
            'Magento\Setup\Controller\Data\PhpExtensionsController',
            'Magento\Setup\Controller\Data\PhpVersionController',
            'Magento\Setup\Controller\Data\StatesController',
            'Magento\Setup\Controller\Data\DatabaseController',
            'Magento\Setup\Controller\Data\LanguagesController',
        ],
        'instance' => [
            'preference' => [
                'Zend\EventManager\EventManagerInterface' => 'EventManager',
                'Zend\ServiceManager\ServiceLocatorInterface' => 'ServiceManager',
                'Magento\Setup\Module\Dependency\ManagerInterface' => 'Magento\Setup\Module\Dependency\Manager',
                'Magento\Setup\Module\Setup\Connection\AdapterInterface' =>
                    'Magento\Setup\Module\Setup\Connection\Adapter',
                'Magento\Setup\Module\Resource\ResourceInterface' => 'Magento\Setup\Module\Resource\Resource',
                'Magento\Setup\Module\ModuleListInterface' => 'Magento\Setup\Module\ModuleList',
            ]
        ],
    ],
];
