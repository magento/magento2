<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Source;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ApplyTo implements OptionSourceInterface
{
    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * @param ProductTypeListInterface $productTypeList
     */
    public function __construct(
        ProductTypeListInterface $productTypeList
    ) {
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
