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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    array('id' => 'Dummy1::parent', 'title' => 'Dummy Parent Resource', 'module' => 'Dummy1', 'sortOrder' => 0,
        'children' => array(
            array('id' => 'Dummy1::first', 'title' => 'Dummy Resource #1', 'module' => 'Dummy1', 'sortOrder' => '0',
                'children' => array(
                    array(
                        'id' => 'Dummy2::parent', 'title' => 'Dummy 2 Resource Parent', 'module' => 'Dummy2',
                        'sortOrder' => '0',
                        'children' => array(
                            array(
                                'id' => 'Dummy2::first',
                                'title' => 'Dummy 2 Resource #1',
                                'module' => 'Dummy2',
                                'sortOrder' => '10'
                            ),
                            array(
                                'id' => 'Dummy2::second',
                                'title' => 'Dummy 2 Resource #2',
                                'module' => 'Dummy2',
                                'sortOrder' => '20'
                            ),
                        )
                    )
                )
            ),
            array('id' => 'Dummy1::second', 'title' => 'Dummy Resource #2', 'module' => 'Dummy1', 'sortOrder' => '10'),
            array('id' => 'Dummy1::third', 'title' => 'Dummy Resource #3', 'module' => 'Dummy1', 'sortOrder' => '50')
        )
    ),
    array('id' => 'Dummy1::all', 'title' => 'Allow everything', 'module' => 'Dummy1', 'sortOrder' => 0)
);
