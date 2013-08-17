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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'node_one' => array(
        '__attributes__' => array('id' => 'resource_1', 'title' => 'Resource 1', 'sortOrder' => 1, 'disabled' => '0'),
        'resource' => array(
            array(
                '__attributes__' => array(
                    'id' => 'resource_1.3', 'title' => 'Resource 1.3', 'disabled' => '1', 'sortOrder' => 2
                ),
            ),
            array(
                '__attributes__' => array(
                    'id' => 'resource_1.1', 'title' => 'Resource 1.1', 'disabled' => 1
                ),
            ),
            array(
                '__attributes__' => array(
                    'id' => 'resource_1.2', 'title' => 'Resource 1.2', 'sortOrder' => 1, 'disabled' => 'false'
                ),
            ),
        ),
    ),
    'node_two' => array(
        '__attributes__' => array('id' => 'resource_2', 'title' => 'Resource 2', 'disabled' => 'true'),
    ),
);
