<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;

/**
 * Cron expression validator interface
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * Perform validation of cron expression
     *
     * @param ExpressionInterface $expression
     *
     * @return bool
     */
    public function validate(ExpressionInterface $expression);
}
