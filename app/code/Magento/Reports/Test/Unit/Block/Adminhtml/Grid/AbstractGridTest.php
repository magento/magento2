<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Grid\AbstractGrid;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
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
            AbstractGrid::class,
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
            ['getBaseCurrencyCode', 'getCurrentCurrencyCode']
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
