<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Text;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Text\AbstractText;

/**
 * Data provider for custom options from text group with type "field".
 */
class Field extends AbstractText
{
    /**
     * @inheritdoc
     */
    protected function getType(): string
    {
        return ProductCustomOptionInterface::OPTION_TYPE_FIELD;
    }
}
