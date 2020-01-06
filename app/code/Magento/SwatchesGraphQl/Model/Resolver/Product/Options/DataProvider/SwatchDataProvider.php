<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchesGraphQl\Model\Resolver\Product\Options\DataProvider;

use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media as SwatchesMedia;
use Magento\Swatches\Model\Swatch;

/**
 * Data provider for options swatches.
 */
class SwatchDataProvider
{
    /**
     * @var SwatchData
     */
    private $swatchHelper;

    /**
     * @var SwatchesMedia
     */
    private $swatchMediaHelper;

    /**
     * SwatchDataProvider constructor.
     *
     * @param SwatchData $swatchHelper
     * @param SwatchesMedia $swatchMediaHelper
     */
    public function __construct(
        SwatchData $swatchHelper,
        SwatchesMedia $swatchMediaHelper
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
    }

    /**
     * Returns swatch data by option ID.
     *
     * @param string $optionId
     * @return array|null
     */
    public function getData(string $optionId): ?array
    {
        $swatches = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);
        if (!isset($swatches[$optionId]['type'], $swatches[$optionId]['value'])) {
            return null;
        }
        $type = (int)$swatches[$optionId]['type'];
        $value = $swatches[$optionId]['value'];
        $data = ['value' => $value, 'type' => $type];
        if ($type === Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
            $data['thumbnail'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $value
            );
        }
        return $data;
    }
}
