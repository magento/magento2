<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Directory\Model\Currency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Ui\Component\Listing\Column\Price;

/**
 * Class PriceTest
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Price
     */
    protected $model;

    /**
     * @var Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->currencyMock = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->setMethods(['load', 'format'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\Price::class,
            ['currency' => $this->currencyMock, 'context' => $contextMock]
        );
    }

    public function testPrepareDataSource()
    {
        $itemName = 'itemName';
        $oldItemValue = 'oldItemValue';
        $newItemValue = 'newItemValue';
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        $itemName => $oldItemValue,
                        'base_currency_code' => 'US'
                    ]
                ]
            ]
        ];

        $this->currencyMock->expects($this->once())
            ->method('load')
            ->with($dataSource['data']['items'][0]['base_currency_code'])
            ->willReturnSelf();

        $this->currencyMock->expects($this->once())
            ->method('format')
            ->with($oldItemValue, [], false)
            ->willReturn($newItemValue);

        $this->model->setData('name', $itemName);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
