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
    'connection' => array(
        'connection_one' => array('name' => 'connection_one', 'dbName' => 'overridden_db_one'),
        'connection_new' => array('name' => 'connection_new', 'dbName' => 'db_new')
    ),
    'resource' => array(
        'resource_one' => array('name' => 'resource_one', 'connection' => 'connection_new'),
        'resource_new' => array('name' => 'resource_new', 'connection' => 'connection_two')
    ),
    'cache' => array(
        'frontend' => array(
            'cache_frontend_one' => array('name' => 'cache_frontend_one', 'backend' => 'memcached'),
            'cache_frontend_new' => array('name' => 'cache_frontend_new', 'backend' => 'apc')
        ),
        'type' => array(
            'cache_type_one' => array('name' => 'cache_type_one', 'frontend' => 'cache_frontend_new'),
            'cache_type_new' => array('name' => 'cache_type_new', 'frontend' => 'cache_frontend_two')
        )
    ),
    'arbitrary_one' => 'Overridden Value One',
    'arbitrary_new' => 'Value New'
);
