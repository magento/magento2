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
    'config' => array(
        'acl' => array(
            'resources' => array(
                array(
                    'id' => 'Custom_Module::resource_one',
                    'title' => 'Resource One Title',
                    'sortOrder' => 10,
                    'disabled' => true,
                    'children' => array()
                ),
                array(
                    'id' => 'Custom_Module::resource_two',
                    'title' => 'Resource Two Title',
                    'sortOrder' => 20,
                    'disabled' => false,
                    'children' => array()
                ),
                array(
                    'id' => 'Custom_Module::parent_resource',
                    'title' => 'Parent Resource Title',
                    'sortOrder' => 50,
                    'disabled' => false,
                    'children' => array(
                        array(
                            'id' => 'Custom_Module::child_resource_one',
                            'title' => 'Resource Child Title',
                            'sortOrder' => 30,
                            'disabled' => false,
                            'children' => array(
                                array(
                                    'id' => 'Custom_Module::child_resource_two',
                                    'title' => 'Resource Child Level 2 Title',
                                    'sortOrder' => 10,
                                    'disabled' => false,
                                    'children' => array()
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);
