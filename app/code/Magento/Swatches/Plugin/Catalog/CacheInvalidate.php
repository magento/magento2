<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Plugin\Catalog;

use \Magento\Framework\App\Cache\Type\Block;
use \Magento\Framework\App\Cache\Type\Collection;

class CacheInvalidate
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $typeList;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Magento\Swatches\Helper\Data $swatchHelper
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
