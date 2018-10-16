<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultExtensionInterface;

class ProductSalableResult extends AbstractExtensibleModel implements ProductSalableResultInterface
{
    /**
     * @var ProductSalabilityErrorInterface[]
     */
    private $errors = [];

    /**
     * @param ProductSalabilityErrorInterface[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @inheritdoc
     */
    public function isSalable(): bool
    {
        return empty($this->errors);
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?ProductSalableResultExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(ProductSalableResultInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(ProductSalableResultExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
