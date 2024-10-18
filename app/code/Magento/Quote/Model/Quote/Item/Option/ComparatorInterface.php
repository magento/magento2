<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item\Option;

use Magento\Framework\DataObject;

/**
 * Quote item options comparator
 */
interface ComparatorInterface
{
    /**
     * Compare two quote item options
     *
     * @param DataObject $option1
     * @param DataObject $option2
     * @return bool
     */
    public function compare(DataObject $option1, DataObject $option2): bool;
}
