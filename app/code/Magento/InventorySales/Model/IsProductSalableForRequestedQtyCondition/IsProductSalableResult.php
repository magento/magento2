<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

class IsProductSalableResult implements IsProductSalableResultInterface
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
}
