<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

/**
 * Product attribute data for attribute with input type multiple select.
 */
class MultipleSelect extends AbstractAttributeDataWithOptions
{
    /**
     * @inheritdoc
     */
    protected function getFrontendInput(): string
    {
        return 'multiselect';
    }
}
