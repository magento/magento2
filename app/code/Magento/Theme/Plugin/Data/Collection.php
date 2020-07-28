<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin\Data;

/**
 * Plugin to return last page if current page greater then collection size.
 */
class Collection
{
    /**
     * Return last page if current page greater then last page.
     *
     * @param \Magento\Framework\Data\Collection $subject
     * @param int $result
     * @param int $displacement
     * @return int
     */
    public function afterGetCurPage(\Magento\Framework\Data\Collection $subject, int $result, int $displacement = 0)
    {
        if ($result > $subject->getLastPageNumber()) {
            $result = $subject->getLastPageNumber();
        }

        return $result;
    }
}
