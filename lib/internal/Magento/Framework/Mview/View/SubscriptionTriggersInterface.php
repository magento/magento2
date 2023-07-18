<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

use Magento\Framework\DB\Ddl\Trigger;

/**
 * Extended Interface of \Magento\Framework\Mview\View\SubscriptionInterface
 */
interface SubscriptionTriggersInterface
{
    /**
     * Get all triggers for the subscription
     *
     * @return Trigger[]
     */
    public function getTriggers();

    /**
     * Save a trigger to the DB
     *
     * @param Trigger $trigger
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function saveTrigger(Trigger $trigger);
}
