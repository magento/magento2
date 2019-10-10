<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Select;

use Magento\Catalog\Model\Product\Option\Create\DataProvider\Type\Select\AbstractSelect;

/**
 * Data provider for custom options from select group with type "Drop-down".
 */
class DropDown extends AbstractSelect
{
    /**
     * @inheritdoc
     */
    protected function getType(): string
    {
        return 'drop_down';
    }
}
