<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\AttributeSet;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Set Options to Attribute Set.
 */
class Options implements OptionSourceInterface
{
    /**
     * @var null|array
     */
    protected $options;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Product $product
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Product $product
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
    }

    /**
     * @inheritdoc
     *
     * @return array|null
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = $this->collectionFactory->create()
                ->setEntityTypeFilter($this->product->getTypeId())
                ->toOptionArray();

            array_walk(
                $this->options,
                function (&$option) {
                    $option['__disableTmpl'] = true;
                }
            );
        }

        return $this->options;
    }
}
