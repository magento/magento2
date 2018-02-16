<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;

/**
 * Cron expression matcher interface
 *
 * @api
 */
interface MatcherInterface
{
    /**
     * Perform match of cron expression against timestamp
     *
     * @param ExpressionInterface $expression
     * @param int                 $timestamp
     *
     * @return bool
     */
    public function match(ExpressionInterface $expression, $timestamp);
}
