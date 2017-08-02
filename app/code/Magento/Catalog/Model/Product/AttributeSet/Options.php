<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\AttributeSet;

/**
 * Class \Magento\Catalog\Model\Product\AttributeSet\Options
 *
 * @since 2.0.0
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var null|array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $product
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            $this->options = $this->collectionFactory->create()
                ->setEntityTypeFilter($this->product->getTypeId())
                ->toOptionArray();
        }
        return $this->options;
    }
}
