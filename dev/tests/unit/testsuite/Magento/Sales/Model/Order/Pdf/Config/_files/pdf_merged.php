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
    'renderers' => array(
        'type_one' => array(
            'product_type_one' => 'Renderer\Type\One\Product\One',
            'product_type_two' => 'Renderer\Type\One\Product\Two',
        ),
        'type_two' => array(
            'product_type_three' => 'Renderer\Type\Two\Product\Two',
        ),
    ),
    'totals' => array(
        'total1' => array(
            'title' => 'Title1 Modified',
            'source_field' => 'source1',
            'title_source_field' => 'title_source1',
            'font_size' => '1',
            'display_zero' => 'false',
            'sort_order' => '1',
            'model' => 'Model1',
            'amount_prefix' => 'prefix1',
        ),
        'total2' => array(
            'title' => 'Title2',
            'source_field' => 'source2',
            'title_source_field' => 'title_source2',
            'font_size' => '2',
            'display_zero' => 'true',
            'sort_order' => '2',
            'model' => 'Model2',
            'amount_prefix' => 'prefix2',
        ),
        'total3' => array(
            'title' => 'Title3',
            'source_field' => 'source3',
            'title_source_field' => 'title_source3',
            'font_size' => '3',
            'display_zero' => 'false',
            'sort_order' => '3',
            'model' => 'Model3',
            'amount_prefix' => 'prefix3',
        ),
    ),
);
