<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * New attribute panel on product edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Magento\Backend\Block\Widget\Button;

class Create extends Button
{
    /**
     * Config of create new attribute
     *
     * @var \Magento\Framework\Object
     */
    protected $_config = null;

    /**
     * Retrieve config of new attribute creation
     *
     * @return \Magento\Framework\Object
     */
    public function getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = new \Magento\Framework\Object();
        }

        return $this->_config;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->setId(
            'create_attribute_' . $this->getConfig()->getGroupId()
        )->setType(
            'button'
        )->setClass(
            'action-add'
        )->setLabel(
            __('New Attribute')
        )->setDataAttribute(
            [
                'mage-init' => [
                    'productAttributes' => [
                        'url' => $this->getUrl(
                            'catalog/product_attribute/new',
                            [
                                'group' => $this->getConfig()->getAttributeGroupCode(),
                                'store' => $this->getConfig()->getStoreId(),
                                'product' => $this->getConfig()->getProductId(),
                                'type' => $this->getConfig()->getTypeId(),
                                'popup' => 1
                            ]
                        ),
                    ],
                ],
            ]
        );

        $this->getConfig()->setUrl(
            $this->getUrl(
                'catalog/product_attribute/new',
                [
                    'group' => $this->getConfig()->getAttributeGroupCode(),
                    'store' => $this->getConfig()->getStoreId(),
                    'product' => $this->getConfig()->getProductId(),
                    'type' => $this->getConfig()->getTypeId(),
                    'popup' => 1
                ]
            )
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setCanShow(true);
        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_edit_tab_attributes_create_html_before',
            ['block' => $this]
        );
        if (!$this->getCanShow()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId() . 'JsObject';
    }
}
