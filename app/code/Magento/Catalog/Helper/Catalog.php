<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

/**
 * Adminhtml Catalog helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Attribute Tab block name for product edit
     *
     * @var string
     * @since 2.0.0
     */
    protected $_attributeTabBlock = null;

    /**
     * Attribute Tab block name for category edit
     *
     * @var string
     * @since 2.0.0
     */
    protected $_categoryAttributeTabBlock;

    /**
     * Retrieve Attribute Tab Block Name for Product Edit
     *
     * @return string
     * @since 2.0.0
     */
    public function getAttributeTabBlock()
    {
        return $this->_attributeTabBlock;
    }

    /**
     * Set Custom Attribute Tab Block Name for Product Edit
     *
     * @param string $attributeTabBlock
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }

    /**
     * Retrieve Attribute Tab Block Name for Category Edit
     *
     * @return string
     * @since 2.0.0
     */
    public function getCategoryAttributeTabBlock()
    {
        return $this->_categoryAttributeTabBlock;
    }

    /**
     * Set Custom Attribute Tab Block Name for Category Edit
     *
     * @param string $attributeTabBlock
     * @return $this
     * @since 2.0.0
     */
    public function setCategoryAttributeTabBlock($attributeTabBlock)
    {
        $this->_categoryAttributeTabBlock = $attributeTabBlock;
        return $this;
    }
}
