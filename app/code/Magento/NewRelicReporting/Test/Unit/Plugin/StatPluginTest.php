<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Plugin;

use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Plugin\StatPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatPluginTest extends TestCase
{
    private const STAT_NAME_NOT_CRON_JOB = 'NotCronJob';
    private const STAT_NAME_CRON_JOB = StatPlugin::TIMER_NAME_CRON_PREFIX . 'Name';

    /**
     * @var StatPlugin
     */
    private $statPlugin;

    /**
     * @var MockObject|NewRelicWrapper
     */
    private $newRelicWrapperMock;

    /**
     * @var MockObject|Stat
     */
    private $statMock;

    /**
     * Build class for testing
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->statPlugin = $objectManager->getObject(StatPlugin::class, [
            'newRelicWrapper' => $this->getNewRelicWrapperMock()
        ]);

        $this->statMock = $this->getMockBuilder(Stat::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Expects that NewRelic wrapper will never be called
     */
    public function testNewRelicTransactionNameIsNotSetIfNotCronjobPattern()
    {
        $this->newRelicWrapperMock
            ->expects($this->never())
            ->method('setTransactionName');
        $this->newRelicWrapperMock
            ->expects($this->never())
            ->method('endTransaction');

        $this->statPlugin->beforeStart($this->statMock, self::STAT_NAME_NOT_CRON_JOB);
        $this->statPlugin->beforeStop($this->statMock, self::STAT_NAME_NOT_CRON_JOB);
    }

    /**
     * NewRelic Wrapper is called when Task name fits Cron Job pattern
     */
    public function testNewRelicTransactionNameIsSetForCronjobNamePattern()
    {
        $this->newRelicWrapperMock
            ->expects($this->once())
            ->method('setTransactionName');
        $this->newRelicWrapperMock
            ->expects($this->once())
            ->method('endTransaction');

        $this->statPlugin->beforeStart($this->statMock, self::STAT_NAME_CRON_JOB);
        $this->statPlugin->beforeStop($this->statMock, self::STAT_NAME_CRON_JOB);
    }

    /**
     * @return NewRelicWrapper
     */
    private function getNewRelicWrapperMock(): NewRelicWrapper
    {
        if (null === $this->newRelicWrapperMock) {
            $this->newRelicWrapperMock = $this->getMockBuilder(NewRelicWrapper::class)
                ->disableOriginalConstructor()
                ->setMethods(['setTransactionName', 'endTransaction'])
                ->getMock();
        }

        return $this->newRelicWrapperMock;
    }
}
