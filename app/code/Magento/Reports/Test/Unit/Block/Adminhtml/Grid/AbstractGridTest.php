<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Grid\AbstractGrid;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid.
 */
class AbstractGridTest extends TestCase
{
    /**
     * @var AbstractGrid|MockObject
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
            AbstractGrid::class,
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
            ['getCurrentCurrencyCode', 'getBaseCurrencyCode']
        );

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->model->setStoreIds($storeIds);

        if ($storeIds) {
            $storeMock->expects($this->once())->method('getCurrentCurrencyCode')->willReturn('EUR');
            $expectedCurrencyCode = 'EUR';
        } else {
            $storeMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
            $expectedCurrencyCode = 'USD';
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
