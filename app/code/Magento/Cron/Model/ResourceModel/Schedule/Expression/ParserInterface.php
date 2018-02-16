<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;

/**
 * Cron expression parser interface
 *
 * @api
 */
interface ParserInterface
{
    /**
     * Perform parsing of cron expression
     *
     * @param ExpressionInterface $expression
     *
     * @return bool|PartInterface[]
     */
    public function parse(ExpressionInterface $expression);
}
