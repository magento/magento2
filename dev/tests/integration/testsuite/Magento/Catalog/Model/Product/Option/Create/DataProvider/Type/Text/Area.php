<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Text;

use Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Text\AbstractText;

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
        return 'area';
    }
}
