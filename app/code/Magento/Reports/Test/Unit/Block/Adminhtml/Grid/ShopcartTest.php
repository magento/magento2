<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Grid\Shopcart;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magento\Reports\Block\Adminhtml\Grid\Shopcart.
 */
class ShopcartTest extends TestCase
{
    /**
     * @var Shopcart|MockObject
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->model = $objectManager->getObject(
            Shopcart::class,
            ['_storeManager' => $this->storeManagerMock]
        );
    }

    /**
     * @param $storeIds
     */
    #[DataProvider('getCurrentCurrencyCodeDataProvider')]
    public function testGetCurrentCurrencyCode($storeIds)
    {
        $storeMock = $this->createPartialMock(
            Store::class,
            ['getBaseCurrencyCode']
        );

        $this->model->setStoreIds($storeIds);

        if ($storeIds) {
            $expectedCurrencyCode = 'EUR';
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->with($storeIds[0])
                ->willReturn($storeMock);
            $storeMock->expects($this->once())
                ->method('getBaseCurrencyCode')
                ->willReturn($expectedCurrencyCode);
        } else {
            $expectedCurrencyCode = 'USD';
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->with(1)
                ->willReturn($storeMock);
            $this->storeManagerMock->expects($this->once())
                ->method('getStores')
                ->willReturn([1 => $storeMock]);
            $storeMock->expects($this->once())
                ->method('getBaseCurrencyCode')
                ->willReturn($expectedCurrencyCode);
        }

        $currencyCode = $this->model->getCurrentCurrencyCode();
        $this->assertEquals($expectedCurrencyCode, $currencyCode);
    }

    /**
     * DataProvider for testGetCurrentCurrencyCode.
     *
     * @return array
     */
    public static function getCurrentCurrencyCodeDataProvider()
    {
        return [
            [[]],
            [[2]],
        ];
    }
}
