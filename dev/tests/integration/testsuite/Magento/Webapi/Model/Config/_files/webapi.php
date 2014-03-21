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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    '\Magento\TestModule1\Service\V1\AllSoapAndRestInterface' => array(
        'class' => '\Magento\TestModule1\Service\V1\AllSoapAndRestInterface',
        'methods' => array(
            'item' => array(
                'httpMethod' => 'GET',
                'method' => 'item',
                'route' => '/:id',
                'isSecure' => false,
                'resources' => array('Magento_TestModule1::resource1')
            ),
            'create' => array(
                'httpMethod' => 'POST',
                'method' => 'create',
                'route' => '',
                'isSecure' => false,
                'resources' => array('Magento_TestModule1::resource2')
            )
        ),
        'baseUrl' => '/V1/testmodule1'
    ),
    '\Magento\TestModule1\Service\V2\AllSoapAndRestInterface' => array(
        'class' => '\Magento\TestModule1\Service\V2\AllSoapAndRestInterface',
        'methods' => array(
            'item' => array(
                'httpMethod' => 'GET',
                'method' => 'item',
                'route' => '/:id',
                'isSecure' => false,
                'resources' => array('Magento_TestModule1::resource1')
            ),
            'create' => array(
                'httpMethod' => 'POST',
                'method' => 'create',
                'route' => '',
                'isSecure' => false,
                'resources' => array('Magento_TestModule1::resource1', 'Magento_TestModule1::resource2')
            ),
            'delete' => array(
                'httpMethod' => 'DELETE',
                'method' => 'delete',
                'route' => '/:id',
                'isSecure' => true,
                'resources' => array('Magento_TestModule1::resource2')
            )
        ),
        'baseUrl' => '/V2/testmodule1'
    ),
    '\Magento\TestModule2\Service\V2\AllSoapAndRestInterface' => array(
        'class' => '\Magento\TestModule2\Service\V2\AllSoapAndRestInterface',
        'methods' => array(
            'update' => array(
                'httpMethod' => 'PUT',
                'method' => 'update',
                'route' => '',
                'isSecure' => false,
                'resources' => array('Magento_TestModule1::resource1')
            ),
            'delete' => array(
                'httpMethod' => 'DELETE',
                'method' => 'delete',
                'route' => '/:id',
                'isSecure' => true,
                'resources' => array('Magento_TestModule1::resource2')
            )
        ),
        'baseUrl' => '/V2/testmodule2'
    )
);
