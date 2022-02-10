<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Model\Quote;

use Magento\Framework\DB\Helper;

/**
 *  Get datetime difference in days
 */
class DatetimeDiffPlugin
{
    /**
     * Get date difference in days between two dates given in 'Y-m-d H:i:s' format
     *
     * @param Helper $subject
     * @param \Closure $proceed
     * @param mixed $startDate
     * @param mixed $endDate
     * @return \Zend_Db_Expr
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDateDiff(
        Helper $subject,
        \Closure $proceed,
        $startDate,
        $endDate
    ): \Zend_Db_Expr
    {
        $dateDiff = "ABS(TIMESTAMPDIFF(DAY, {$endDate}, {$startDate}))";
        return new \Zend_Db_Expr($dateDiff);
    }
}
