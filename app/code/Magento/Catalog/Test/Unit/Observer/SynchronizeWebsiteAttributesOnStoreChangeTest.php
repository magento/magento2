<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific\Scheduler;
use Magento\Catalog\Observer\SynchronizeWebsiteAttributesOnStoreChange;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

class SynchronizeWebsiteAttributesOnStoreChangeTest extends TestCase
{
    /**
     * @param $invalidDataObject
     * @dataProvider executeInvalidStoreDataProvider
     */
    public function testExecuteInvalidStore($invalidDataObject)
    {
        $eventObserver = new Observer([
            'data_object' => $invalidDataObject,
        ]);

        $schedulerMock = $this->createMock(Scheduler::class);
        $schedulerMock->expects(self::never())
            ->method('execute');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($schedulerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public static function executeInvalidStoreDataProvider()
    {
        return [
            [
                ['invalidDataObject'],
            ],
        ];
    }

    /**
     * @param \Closure $store
     * @dataProvider executeStoreHasNoChangesDataProvider
     */
    public function testExecuteStoreHasNoChanges(\Closure $store)
    {
        $store = $store($this);
        $eventObserver = new Observer([
            'data_object' => $store,
        ]);

        $schedulerMock = $this->createMock(Scheduler::class);
        $schedulerMock->expects(self::never())
            ->method('execute');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($schedulerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    protected function getMockForStoreClass()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasDataChanges',
                'getOrigData',
            ])
            ->getMock();

        $store->expects($this->once())
            ->method('hasDataChanges')
            ->willReturn(
                false
            );

        $store->expects($this->never())
            ->method('getOrigData');

        return $store;
    }

    /**
     * @return array
     */
    public static function executeStoreHasNoChangesDataProvider()
    {
        $store = static fn (self $testCase) => $testCase->getMockForStoreClass();
        return [
            [
                $store,
            ],
        ];
    }

    /**
     * @param \Closure $store
     * @dataProvider executeWebsiteIdIsNoChangedAndNotNewDataProvider
     */
    public function testExecuteWebsiteIdIsNoChangedAndNotNew(\Closure $store)
    {
        $store = $store($this);

        $eventObserver = new Observer([
            'data_object' => $store,
        ]);

        $schedulerMock = $this->createMock(Scheduler::class);
        $schedulerMock->expects(self::never())
            ->method('execute');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($schedulerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public static function executeWebsiteIdIsNoChangedAndNotNewDataProvider()
    {
        $store = static fn (self $testCase) => $testCase->getMockForStoreNew(false);
        return [
            [
                $store,
            ],
        ];
    }

    /**
     * @param \Closure $store
     * @dataProvider executeSuccessDataProvider
     */
    public function testExecuteSuccess(\Closure $store)
    {
        $store = $store($this);
        $eventObserver = new Observer([
            'data_object' => $store,
        ]);

        $schedulerMock = $this->createMock(Scheduler::class);
        $schedulerMock->expects(self::once())
            ->method('execute');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($schedulerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    protected function getMockForStoreNew($return)
    {
        $sameWebsiteId = 1;
        $storeNew = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasDataChanges',
                'getOrigData',
                'getWebsiteId',
                'isObjectNew',
            ])
            ->getMock();

        $storeNew->expects($this->once())
            ->method('hasDataChanges')
            ->willReturn(
                true
            );

        $storeNew->expects($this->once())
            ->method('getOrigData')
            ->with('website_id')
            ->willReturn(
                $sameWebsiteId
            );

        $storeNew->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(
                $sameWebsiteId
            );

        $storeNew->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(
                $return
            );

        return $storeNew;
    }

    protected function getStoreChangeWebsite()
    {
        $sameWebsiteId = 1;
        $newWebsiteId = 2;

        $storeChangedWebsite = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasDataChanges',
                'getOrigData',
                'getWebsiteId',
                'isObjectNew',
            ])
            ->getMock();

        $storeChangedWebsite->expects($this->once())
            ->method('hasDataChanges')
            ->willReturn(
                true
            );

        $storeChangedWebsite->expects($this->once())
            ->method('getOrigData')
            ->with('website_id')
            ->willReturn(
                $sameWebsiteId
            );

        $storeChangedWebsite->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(
                $newWebsiteId
            );

        $storeChangedWebsite->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(
                false
            );

        return $storeChangedWebsite;
    }
    /**
     * @return array
     */
    public static function executeSuccessDataProvider()
    {
        $storeNew = static fn (self $testCase) => $testCase->getMockForStoreNew(true);

        $storeChangedWebsite = static fn (self $testCase) => $testCase->getStoreChangeWebsite();

        return [
            [
                $storeNew,
            ],
            [
                $storeChangedWebsite,
            ],
        ];
    }
}
