<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Web;

use Magento\Catalog\Model\Config\CatalogMediaConfig;

/**
 * Option provider for catalog media URL format system setting.
 */
class CatalogMediaUrlFormat implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get a list of supported catalog media URL formats.
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => CatalogMediaConfig::IMAGE_OPTIMIZATION_PARAMETERS,
                'label' => __('Image optimization based on query parameters')
            ],
            ['value' => CatalogMediaConfig::HASH, 'label' => __('Unique hash per image variant (Legacy mode)')]
        ];
    }
}
