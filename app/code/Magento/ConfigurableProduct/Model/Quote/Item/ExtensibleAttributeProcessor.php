<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item;

class ExtensibleAttributeProcessor implements \Magento\Quote\Model\Quote\Item\ExtensibleAttributeProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function convertAttributesToBuyRequest(\Magento\Quote\Api\Data\ProductOptionExtensionInterface $attributes)
    {
        //Processor implementation
        return null;
    }
}
