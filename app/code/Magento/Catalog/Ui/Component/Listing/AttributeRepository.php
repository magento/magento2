<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing;

class AttributeRepository extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var null|\Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    private $attributes;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Collection $attributeCollection
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\Collection $attributeCollection,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getList()
    {
        if (null == $this->attributes) {
            $this->attributes = [];
            $this->attributeCollection->addIsUsedInGridFilter();
            foreach ($this->attributeCollection as $attribute) {
                $attribute = $this->productAttributeRepository->get($attribute->getAttributeId());
                $this->attributes[] = $attribute;
            }
        }
        return $this->attributes;
    }
}
