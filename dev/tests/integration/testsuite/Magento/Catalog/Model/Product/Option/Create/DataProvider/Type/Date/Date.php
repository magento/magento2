<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Date;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option\Create\DataProvider\AbstractBase;

/**
 * Data provider for custom options from date group with type "date".
 */
class Date extends AbstractBase
{
    /**
     * @inheritdoc
     */
    protected function getType(): string
    {
        return ProductCustomOptionInterface::OPTION_GROUP_DATE;
    }
}
