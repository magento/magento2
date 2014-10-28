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
    'sales_widget_guestform' => array(
        '@' => array('type' => 'Magento\Sales\Block\Widget\Guest\Form'),
        'is_email_compatible' => '1',
        'name' => 'Orders and Returns',
        'description' => 'Orders and Returns Search Form',
        'parameters' => array(
            'title' => array('type' => 'text', 'visible' => '0', 'label' => 'Anchor Custom Title'),
            'template' => array(
                'type' => 'select',
                'value' => 'hierarchy/widget/link/link_block.phtml',
                'values' => array(
                    'default' => array(
                        'value' => 'hierarchy/widget/link/link_block.phtml',
                        'label' => 'CMS Page Link Block Template'
                    ),
                    'link_inline' => array(
                        'value' => 'hierarchy/widget/link/link_inline.phtml',
                        'label' => 'CMS Page Link Inline Template'
                    )
                ),
                'visible' => '0'
            ),
            'link_display' => array(
                'source_model' => 'Magento\Backend\Model\Config\Source\Yesno',
                'type' => 'select',
                'visible' => '1',
                'sort_order' => '10',
                'label' => 'Display a Link to Loading a Spreadsheet',
                'description' => "Defines whether a link to My Account"
            ),
            'link_text' => array(
                'type' => 'text',
                'value' => 'Load a list of SKUs',
                'visible' => '1',
                'required' => '1',
                'sort_order' => '20',
                'label' => 'Link Text',
                'description' => 'The text of the link to the My Account &gt; Order by SKU page',
                'depends' => array('link_display' => array('value' => '1'))
            ),
            'id_path' => array(
                'type' => 'label',
                '@' => array('type' => 'complex'),
                'helper_block' => array(
                    'type' => 'Magento\Backend\Block\Catalog\Product\Widget\Chooser',
                    'data' => array('button' => array('open' => 'Select Product...'))
                ),
                'visible' => '1',
                'required' => '1',
                'sort_order' => '10',
                'label' => 'Product'
            )
        ),
        'supported_containers' => array(
            '0' => array('container_name' => 'left', 'template' => array('default' => 'default_template')),
            '1' => array('container_name' => 'right', 'template' => array('default' => 'default_template'))
        )
    )
);
