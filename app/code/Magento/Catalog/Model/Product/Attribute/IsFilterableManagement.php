<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
