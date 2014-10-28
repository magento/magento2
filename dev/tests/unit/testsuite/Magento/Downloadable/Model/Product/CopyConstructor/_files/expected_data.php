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
    'link' => array(
        array(
            'is_delete' => false,
            'link_id' => null,
            'title' => 'title',
            'is_shareable' => 'is_shareable',
            'sample' => array(
                'type' => 'sample_type',
                'url' => 'sample_url',
                'file' => array(array('file' => 'sample_file', 'name' => 'sample_file', 'size' => 0, 'status' => null))
            ),
            'file' => array(array('file' => 'link_file', 'name' => 'link_file', 'size' => 0, 'status' => null)),
            'type' => 'link_type',
            'link_url' => 'link_url',
            'sort_order' => 'sort_order',
            'number_of_downloads' => 'number_of_downloads',
            'price' => 'price'
        )
    ),
    'sample' => array(
        array(
            'is_delete' => false,
            'sample_id' => null,
            'title' => 'title',
            'type' => 'sample_type',
            'file' => array(array('file' => 'sample_file', 'name' => 'sample_file', 'size' => 0, 'status' => null)),
            'sample_url' => 'sample_url',
            'sort_order' => 'sort_order'
        )
    )
);
