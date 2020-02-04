<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;
use Magento\Catalog\Observer\SynchronizeWebsiteAttributesOnStoreChange;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\Store;

class SynchronizeWebsiteAttributesOnStoreChangeTest extends \PHPUnit\Framework\TestCase
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

        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'scheduleSynchronization',
            ])
            ->getMock();

        $synchronizerMock->expects($this->never())
            ->method('scheduleSynchronization');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($synchronizerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function executeInvalidStoreDataProvider()
    {
        return [
            [
                ['invalidDataObject'],
            ],
        ];
    }

    /**
     * @param Store $store
     * @dataProvider executeStoreHasNoChangesDataProvider
     */
    public function testExecuteStoreHasNoChanges(Store $store)
    {
        $eventObserver = new Observer([
            'data_object' => $store,
        ]);

        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'scheduleSynchronization',
            ])
            ->getMock();

        $synchronizerMock->expects($this->never())
            ->method('scheduleSynchronization');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($synchronizerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function executeStoreHasNoChangesDataProvider()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'hasDataChanges',
                'getOrigData',
            ])
            ->getMock();

        $store->expects($this->once())
            ->method('hasDataChanges')
            ->will(
                $this->returnValue(false)
            );

        $store->expects($this->never())
            ->method('getOrigData');

        return [
            [
                $store,
            ],
        ];
    }

    /**
     * @param Store $store
     * @dataProvider executeWebsiteIdIsNoChangedAndNotNewDataProvider
     */
    public function testExecuteWebsiteIdIsNoChangedAndNotNew(Store $store)
    {
        $eventObserver = new Observer([
            'data_object' => $store,
        ]);

        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'scheduleSynchronization',
            ])
            ->getMock();

        $synchronizerMock->expects($this->never())
            ->method('scheduleSynchronization');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($synchronizerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function executeWebsiteIdIsNoChangedAndNotNewDataProvider()
    {
        $sameWebsiteId = 1;
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'hasDataChanges',
                'getOrigData',
                'getWebsiteId',
                'isObjectNew',
            ])
            ->getMock();

        $store->expects($this->once())
            ->method('hasDataChanges')
            ->will(
                $this->returnValue(true)
            );

        $store->expects($this->once())
            ->method('getOrigData')
            ->with('website_id')
            ->will(
                $this->returnValue($sameWebsiteId)
            );

        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will(
                $this->returnValue($sameWebsiteId)
            );

        $store->expects($this->once())
            ->method('isObjectNew')
            ->will(
                $this->returnValue(false)
            );

        return [
            [
                $store,
            ],
        ];
    }

    /**
     * @param Store $store
     * @dataProvider executeSuccessDataProvider
     */
    public function testExecuteSuccess(Store $store)
    {
        $eventObserver = new Observer([
            'data_object' => $store,
        ]);

        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'scheduleSynchronization',
            ])
            ->getMock();

        $synchronizerMock->expects($this->once())
            ->method('scheduleSynchronization');

        $instance = new SynchronizeWebsiteAttributesOnStoreChange($synchronizerMock);
        $result = $instance->execute($eventObserver);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function executeSuccessDataProvider()
    {
        $sameWebsiteId = 1;
        $storeNew = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'hasDataChanges',
                'getOrigData',
                'getWebsiteId',
                'isObjectNew',
            ])
            ->getMock();

        $storeNew->expects($this->once())
            ->method('hasDataChanges')
            ->will(
                $this->returnValue(true)
            );

        $storeNew->expects($this->once())
            ->method('getOrigData')
            ->with('website_id')
            ->will(
                $this->returnValue($sameWebsiteId)
            );

        $storeNew->expects($this->once())
            ->method('getWebsiteId')
            ->will(
                $this->returnValue($sameWebsiteId)
            );

        $storeNew->expects($this->once())
            ->method('isObjectNew')
            ->will(
                $this->returnValue(true)
            );

        $sameWebsiteId = 1;
        $newWebsiteId = 2;
        $storeChangedWebsite = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'hasDataChanges',
                'getOrigData',
                'getWebsiteId',
                'isObjectNew',
            ])
            ->getMock();

        $storeChangedWebsite->expects($this->once())
            ->method('hasDataChanges')
            ->will(
                $this->returnValue(true)
            );

        $storeChangedWebsite->expects($this->once())
            ->method('getOrigData')
            ->with('website_id')
            ->will(
                $this->returnValue($sameWebsiteId)
            );

        $storeChangedWebsite->expects($this->once())
            ->method('getWebsiteId')
            ->will(
                $this->returnValue($newWebsiteId)
            );

        $storeChangedWebsite->expects($this->once())
            ->method('isObjectNew')
            ->will(
                $this->returnValue(false)
            );

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
