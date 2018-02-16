<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class CronJobException used to check that cron handles execution exception
 * @see \Magento\Cron\Test\Unit\Observer\ProcessCronQueueObserverTest::dispatchExceptionInCallbackDataProvider
 */
namespace Magento\Cron\Test\Unit\Model;

class CronJobException
{
    public function execute()
    {
        throw new \Exception('Test exception');
    }
}
