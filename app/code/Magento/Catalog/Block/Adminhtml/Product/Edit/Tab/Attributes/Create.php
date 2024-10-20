<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Magento\Backend\Block\Widget\Button;

/**
 * New attribute panel on product edit page
 */
class Create extends Button
{
    /**
     * Config of create new attribute
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_config = null;

    /**
     * Retrieve config of new attribute creation
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = new \Magento\Framework\DataObject();
        }

        return $this->_config;
    }

    /**
     * Add 'new attribute' button
     *
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
     * Return the HTML for this block
     *
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
     * Return the JavaScript object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId() . 'JsObject';
    }
}
