<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Tax\Api\TaxClassManagementInterface;

class ProductTaxClass extends TaxClass
{
    private const DEFAULT_DATA = [
        'class_type' => TaxClassManagementInterface::TYPE_PRODUCT,
    ];

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply(array_merge(self::DEFAULT_DATA, $data));
    }
}
