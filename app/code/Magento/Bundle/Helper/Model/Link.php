<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    public function cleanExtensionAttribute(array $selectionData): array
    {
        if (array_key_exists(ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY, $selectionData)  &&
            get_class($selectionData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]) !== Magento\Bundle\Api\Data\LinkExtensionInterface::class) {
            unset($selectionData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }
        return $selectionData;
    }
}
