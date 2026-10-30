<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Source;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options for the product attribute "Apply To" field: the available product types.
 */
class ApplyTo implements OptionSourceInterface
{
    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * @param ProductTypeListInterface $productTypeList
     */
    public function __construct(ProductTypeListInterface $productTypeList)
    {
        $this->productTypeList = $productTypeList;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->productTypeList->getProductTypes() as $productType) {
            $result[] = [
                'value' => $productType->getName(),
                'label' => $productType->getLabel()
            ];
        }

        return $result;
    }
}
