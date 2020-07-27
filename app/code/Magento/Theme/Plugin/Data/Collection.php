<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin\Data;

/**
 * Plugin to return real current page even if it greater then collection size.
 *
 * It is necessary to return no values when we reached the last page for api requests
 */
class Collection
{
    public function afterGetCurPage(\Magento\Framework\Data\Collection $subject, int $result, int $displacement = 0)
    {
        if ($result > $subject->getLastPageNumber()) {
            $result = $subject->getLastPageNumber();
        }

        return $result;
    }

}
