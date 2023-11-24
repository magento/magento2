<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ProductAlert\Model\EmailFactory;
use Magento\ProductAlert\Model\Mailing\Publisher;
use Magento\ProductAlert\Model\Observer;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ObserverTest
 *
 * Is used to test Product Alert Observer
 */
class ObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var PriceCollectionFactory|MockObject
     */
    private $priceColFactoryMock;

    /**
     * @var StockCollectionFactory|MockObject
     */
    private $stockColFactoryMock;

    /**
     * @var Publisher
     */
    private $publisherMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->priceColFactoryMock = $this->createMock(PriceCollectionFactory::class);
        $this->stockColFactoryMock = $this->createMock(StockCollectionFactory::class);
        $this->publisherMock = $this->createMock(Publisher::class);

        $this->observer = new Observer(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->priceColFactoryMock,
            $this->stockColFactoryMock,
            $this->publisherMock
        );
    }

    /**
     * Test process alerts with exception in loading websites
     *
     * @return void
     */
    public function testGetWebsitesThrowsException(): void
    {
        $message = 'get website exception';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);

        $this->scopeConfigMock->method('isSetFlag')->willReturn(false);
        $this->storeManagerMock->method('getWebsites')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    /**
     * Test process alerts with exception in creating price collection
     *
     * @return void
     */
    public function testProcessPriceThrowsException(): void
    {
        $message = 'create collection exception';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);

        $groupMock = $this->createMock(\Magento\Store\Model\Group::class);
        $storeMock = $this->createMock(Store::class);
        $groupMock->method('getDefaultStore')->willReturn($storeMock);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getDefaultGroup')->willReturn($groupMock);
        $this->storeManagerMock->method('getWebsites')->willReturn([$websiteMock]);

        $this->scopeConfigMock->method('getValue')->willReturn(true);

        $this->priceColFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    /**
     * Test process alerts with exception in creating stock collection
     *
     * @return void
     */
    public function testProcessStockThrowsException(): void
    {
        $message = 'create collection exception';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);

        $groupMock = $this->createMock(\Magento\Store\Model\Group::class);
        $storeMock = $this->createMock(Store::class);
        $groupMock->method('getDefaultStore')->willReturn($storeMock);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getDefaultGroup')->willReturn($groupMock);
        $this->storeManagerMock->method('getWebsites')->willReturn([$websiteMock]);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->stockColFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }
}
