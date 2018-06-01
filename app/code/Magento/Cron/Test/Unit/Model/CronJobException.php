<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class CronJobException used to check that cron handles execution exception
 * Please see \Magento\Cron\Test\Unit\Model\ObserverTest
 */
namespace Magento\Cron\Test\Unit\Model;

class CronJobException
{
    /**
     * @var \Throwable|null
     */
    private $exception;

    /**
     * @param \Throwable|null $exception
     */
    public function __construct(\Throwable $exception = null)
    {
        $this->exception = $exception;
    }

    /**
     * @throws \Throwable
     */
    public function execute()
    {
        if (!$this->exception) {
            $this->exception = new \Exception('Test exception');
        }
        throw $this->exception;
    }
}
