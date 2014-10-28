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
    '@' => array('type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'),
    'name' => 'CMS Page Link',
    'description' => 'Link to a CMS Page',
    'is_email_compatible' => '1',
    'placeholder_image' => 'Magento_Cms::images/widget_page_link.gif',
    'parameters' => array(
        'page_id' => array(
            '@' => array('type' => 'complex'),
            'type' => 'label',
            'helper_block' => array(
                'type' => 'Magento\Cms\Block\Adminhtml\Page\Widget\Chooser',
                'data' => array('button' => array('open' => 'Select Page...'))
            ),
            'visible' => '1',
            'required' => '1',
            'sort_order' => '10',
            'label' => 'CMS Page'
        ),
        'anchor_text' => array(
            'type' => 'text',
            'visible' => '1',
            'label' => 'Anchor Custom Text',
            'description' => 'If empty, the Page Title will be used',
            'depends' => array('show_pager' => array('value' => '1'))
        ),
        'template' => array(
            'type' => 'select',
            'values' => array(
                'default' => array(
                    'value' => 'product/widget/link/link_block.phtml',
                    'label' => 'Product Link Block Template'
                ),
                'link_inline' => array(
                    'value' => 'product/widget/link/link_inline.phtml',
                    'label' => 'Product Link Inline Template'
                )
            ),
            'visible' => '1',
            'label' => 'Template',
            'value' => 'product/widget/link/link_block.phtml'
        )
    ),
    'supported_containers' => array(
        '0' => array(
            'container_name' => 'left',
            'template' => array('default' => 'default', 'names_only' => 'link_inline')
        ),
        '1' => array('container_name' => 'content', 'template' => array('grid' => 'default', 'list' => 'list'))
    )
);
