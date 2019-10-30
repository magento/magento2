<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\AbstractText;

/**
 * Data provider for custom options from text group with type "area".
 */
class Area extends AbstractText
{
    /**
     * @inheritdoc
     */
    protected function getType(): string
    {
        return ProductCustomOptionInterface::OPTION_TYPE_AREA;
    }
}
