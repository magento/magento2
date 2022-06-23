<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Grid\Shopcart;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
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

        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStore']
        );

        $this->model = $objectManager->getObject(
            Shopcart::class,
            ['_storeManager' => $this->storeManagerMock]
        );
    }

    /**
     * @param $storeIds
     *
     * @dataProvider getCurrentCurrencyCodeDataProvider
     */
    public function testGetCurrentCurrencyCode($storeIds)
    {
        $storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            true,
            true,
            true,
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
    public function getCurrentCurrencyCodeDataProvider()
    {
        return [
            [[]],
            [[2]],
        ];
    }
}
