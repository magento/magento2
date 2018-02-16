<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression encapsulation interface
 *
 * @api
 */
interface ExpressionInterface
{
    /**
     * Set cron expression
     *
     * @param string $cronExpr
     *
     * @throws CronException
     * @return void
     */
    public function setCronExpr($cronExpr);

    /**
     * Get cron expression
     *
     * @return string
     */
    public function getCronExpr();

    /**
     * Get cron expression is valid
     *
     * @return bool
     */
    public function validate();

    /**
     * Get cron expression parts array
     *
     * @return bool|PartInterface[]
     */
    public function getParts();

    /**
     * Match cron expression against timestamp
     *
     * @param int $timestamp
     *
     * @return bool
     */
    public function match($timestamp);

    /**
     * Reset object
     *
     * @return void
     */
    public function reset();
}
