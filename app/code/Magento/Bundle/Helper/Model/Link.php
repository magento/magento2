<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Helper\Model;

use Magento\Framework\Api\ExtensibleDataInterface;

class Link
{
    /**
     * Clean extension attributes
     *
     * @param $selectionData
     *
     * @return array
     */
    public function cleanExtensionAttribute($selectionData): array
    {
        if (array_key_exists(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY, $selectionData)  &&
            get_class($selectionData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]) !== 'Magento\Bundle\Api\Data\LinkExtensionInterface') {
            unset($selectionData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }
        return $selectionData;
    }
}
