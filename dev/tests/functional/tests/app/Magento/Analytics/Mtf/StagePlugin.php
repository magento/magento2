<?php

/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Mtf;

use Magento\Analytics\Model\NotificationTime;
use Magento\Mtf\App\State\AbstractState;

class StagePlugin
{
    /**
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * StagePlugin constructor.
     * @param NotificationTime $notificationTime
     */
    public function __construct(
        NotificationTime $notificationTime
    ) {
        $this->notificationTime = $notificationTime;
    }

    /**
     * @param AbstractState $state
     * @param $result
     */
    public function afterApply(AbstractState $state, $result)
    {
        $this->notificationTime->storeLastTimeNotification(PHP_INT_MAX);
    }
}
