<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect;

class ProductSubselectionInCartConditions extends ProductConditions
{
    public const DEFAULT_DATA = [
        'type' => Subselect::class,
    ];

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply(array_merge(self::DEFAULT_DATA, $data));
    }
}
