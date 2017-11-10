<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Grid;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for class \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid.
 */
class AbstractGridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStore']
        );

        $this->model = $objectManager->getObject(
            \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid::class,
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
            \Magento\Store\Api\Data\StoreInterface::class,
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
    public function getCurrentCurrencyCodeDataProvider()
    {
        return [
            [[]],
            [[2]],
        ];
    }
}
