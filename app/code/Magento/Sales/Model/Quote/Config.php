<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote;

class Config
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    private $_attributeConfig;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     */
    public function __construct(\Magento\Catalog\Model\Attribute\Config $attributeConfig)
    {
        $this->_attributeConfig = $attributeConfig;
    }

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        return $this->_attributeConfig->getAttributeNames('sales_quote_item');
    }
}
