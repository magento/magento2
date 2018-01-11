<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Observer\CatalogCheckIsUsingStaticUrlsAllowedObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CatalogCheckIsUsingStaticUrlsAllowedObserver observer.
 */
class CatalogCheckIsUsingStaticUrlsAllowedObserverTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var CatalogCheckIsUsingStaticUrlsAllowedObserver
     */
    private $model;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->catalogData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            CatalogCheckIsUsingStaticUrlsAllowedObserver::class,
            ['catalogData' => $this->catalogData]
        );
    }

    /**
     * Test observer can correctly handle non integer store id values.
     *
     * @dataProvider executeDataProvider
     * @param string|int $storeId
     * @return void
     */
    public function testExecute($storeId)
    {
        $result = new \stdClass();
        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observer */
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive(
                $this->identicalTo('store_id'),
                $this->identicalTo('result')
            )->willReturnOnConsecutiveCalls(
                $storeId,
                $result
            );
        $observer->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($event);
        $this->catalogData->expects($this->once())
            ->method('setStoreId')
            ->with(0)
            ->willReturnSelf();
        $this->catalogData->expects($this->once())
            ->method('isUsingStaticUrlsAllowed')
            ->willReturn(true);
        $this->model->execute($observer);
        $this->assertTrue($result->isAllowed);
    }

    /**
     * Provide test data for testExecute().
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'store_id' => 0,
            ],
            [
                'store_id' => ''
            ]
        ];
    }
}
