<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Test\Unit\PriceInfo;

use \Magento\Framework\Pricing\PriceInfo\Base;

/**
 * Test class for \Magento\Framework\Pricing\PriceInfo\Base
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\Price\Collection
     */
    protected $priceCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Pricing\Adjustment\Collection
     */
    protected $adjustmentCollection;

    /**
     * @var Base
     */
    protected $model;

    protected function setUp()
    {
        $this->priceCollection = $this->getMock(\Magento\Framework\Pricing\Price\Collection::class, [], [], '', false);
        $this->adjustmentCollection = $this->getMock(
            \Magento\Framework\Pricing\Adjustment\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->model = new Base($this->priceCollection, $this->adjustmentCollection);
    }

    /**
     * test method getPrices()
     */
    public function testGetPrices()
    {
        $this->assertEquals($this->priceCollection, $this->model->getPrices());
    }

    /**
     * @param $entryParams
     * @param $createCount
     * @dataProvider providerGetPrice
     */
    public function testGetPrice($entryParams, $createCount)
    {
        $priceCode = current(array_values(reset($entryParams)));

        $this->priceCollection
            ->expects($this->exactly($createCount))
            ->method('get')
            ->with($this->equalTo($priceCode))
            ->will($this->returnValue('basePrice'));

        foreach ($entryParams as $params) {
            list($priceCode) = array_values($params);
            $this->assertEquals('basePrice', $this->model->getPrice($priceCode));
        }
    }

    /**
     * Data provider for testGetPrice
     *
     * @return array
     */
    public function providerGetPrice()
    {
        return [
            'case with empty quantity' => [
                'entryParams' => [
                    ['priceCode' => 'testCode'],
                ],
                'createCount' => 1,
            ],
            'case with existing price' => [
                'entryParams' => [
                    ['priceCode' => 'testCode'],
                    ['priceCode' => 'testCode'],
                ],
                'createCount' => 2,
            ],
            'case with quantity' => [
                'entryParams' => [
                    ['priceCode' => 'testCode'],
                ],
                'createCount' => 1,
            ],
        ];
    }

    /**
     * @covers \Magento\Framework\Pricing\PriceInfo\Base::getAdjustments
     */
    public function testGetAdjustments()
    {
        $this->adjustmentCollection->expects($this->once())->method('getItems')->will($this->returnValue('result'));
        $this->assertEquals('result', $this->model->getAdjustments());
    }

    /**
     * @covers \Magento\Framework\Pricing\PriceInfo\Base::getAdjustment
     */
    public function testGetAdjustment()
    {
        $this->adjustmentCollection->expects($this->any())->method('getItemByCode')
            ->with('test1')
            ->will($this->returnValue('adjustment'));
        $this->assertEquals('adjustment', $this->model->getAdjustment('test1'));
    }
}
