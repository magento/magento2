<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\AbstractBase;

/**
 * Data provider for custom options from date group with type "time".
 */
class Time extends AbstractBase
{
    /**
     * @inheritdoc
     */
    protected static function getType(): string
    {
        return ProductCustomOptionInterface::OPTION_TYPE_TIME;
    }
}
