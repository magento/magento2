<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Plugin\Catalog;

use \Magento\Framework\App\Cache\Type\Block;
use \Magento\Framework\App\Cache\Type\Collection;

/**
 * Class \Magento\Swatches\Plugin\Catalog\CacheInvalidate
 *
 * @since 2.1.0
 */
class CacheInvalidate
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     * @since 2.1.0
     */
    private $typeList;

    /**
     * @var \Magento\Swatches\Helper\Data
     * @since 2.1.0
     */
    private $swatchHelper;

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->typeList = $typeList;
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $result
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @since 2.1.0
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $result
    ) {
        if ($this->swatchHelper->isSwatchAttribute($subject)) {
            $this->typeList->invalidate(Block::TYPE_IDENTIFIER);
            $this->typeList->invalidate(Collection::TYPE_IDENTIFIER);
        }
        return $result;
    }
}
