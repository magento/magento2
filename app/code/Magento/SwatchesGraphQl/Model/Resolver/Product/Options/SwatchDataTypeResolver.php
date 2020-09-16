<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchesGraphQl\Model\Resolver\Product\Options;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Resolver for swatch data interface.
 */
class SwatchDataTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        switch ($data['type']) {
            case Swatch::SWATCH_TYPE_TEXTUAL:
                return 'TextSwatchData';
            case Swatch::SWATCH_TYPE_VISUAL_COLOR:
                return 'ColorSwatchData';
            case Swatch::SWATCH_TYPE_VISUAL_IMAGE:
                return 'ImageSwatchData';
            default:
                throw new LocalizedException(__('Unsupported swatch type'));
        }
    }
}
