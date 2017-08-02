<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

/**
 * Class \Magento\Quote\Model\Quote\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     * @since 2.0.0
     */
    private $_attributeConfig;

    /**
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Attribute\Config $attributeConfig)
    {
        $this->_attributeConfig = $attributeConfig;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getProductAttributes()
    {
        return $this->_attributeConfig->getAttributeNames('quote_item');
    }
}
