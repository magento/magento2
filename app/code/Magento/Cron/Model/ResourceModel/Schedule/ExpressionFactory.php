<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule;

use Magento\Framework\App\ObjectManager;

/**
 * Cron expression factory
 *
 * @api
 */
class ExpressionFactory
{
    /**
     * Create an expression object
     *
     * @return ExpressionInterface
     */
    public function create()
    {
        /** @var ExpressionInterface $expression */
        return ObjectManager::getInstance()->create(ExpressionInterface::class);
    }
}
