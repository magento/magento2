<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Plugin\Catalog;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Magento\Swatches\Helper\Data as SwatchHelper;

class CacheInvalidate
{
    /**
     * @param CacheTypeListInterface $typeList
     * @param SwatchHelper $swatchHelper
     */
    public function __construct(
        private readonly CacheTypeListInterface $typeList,
        private readonly SwatchHelper $swatchHelper
    ) {
    }

    /**
     * Invalidates block / collection cache when attribute is a swatch.
     *
     * @param Attribute $subject
     * @param Attribute $result
     * @return Attribute
     */
    public function afterSave(
        Attribute $subject,
        Attribute $result
    ) {
        if ($this->swatchHelper->isSwatchAttribute($subject)) {
            $this->typeList->invalidate(Block::TYPE_IDENTIFIER);
            $this->typeList->invalidate(Collection::TYPE_IDENTIFIER);
        }
        return $result;
    }
}
