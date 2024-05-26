<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\ProductAttributeIsFilterableManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class IsFilterableManagement implements ProductAttributeIsFilterableManagementInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function get(string $attributeCode): int
    {
        $attribute = $this->productAttributeRepository->get($attributeCode);

        return (int)$attribute->getIsFilterable();
    }

    /**
     * @inheritdoc
     */
    public function set(string $attributeCode, int $isFilterable): bool
    {
        $attribute = $this->productAttributeRepository->get($attributeCode);
        $attribute->setIsFilterable($isFilterable);
        $this->productAttributeRepository->save($attribute);

        return true;
    }
}
