<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

interface ExtensibleAttributeProcessorInterface
{
    /**
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionInterface $attributes
     * @return \Magento\Framework\DataObject|null
     */
    public function convertAttributesToBuyRequest(\Magento\Quote\Api\Data\ProductOptionExtensionInterface $attributes);
}
