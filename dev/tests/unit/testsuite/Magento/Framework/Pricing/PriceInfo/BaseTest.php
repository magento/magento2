<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Pricing\PriceInfo;

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

    public function setUp()
    {
        $this->priceCollection = $this->getMock('Magento\Framework\Pricing\Price\Collection', [], [], '', false);
        $this->adjustmentCollection = $this->getMock(
            'Magento\Framework\Pricing\Adjustment\Collection',
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
                    ['priceCode' => 'testCode']
                ],
                'createCount' => 1
            ],
            'case with existing price' => [
                'entryParams' => [
                    ['priceCode' => 'testCode'],
                    ['priceCode' => 'testCode']
                ],
                'createCount' => 2
            ],
            'case with quantity' => [
                'entryParams' => [
                    ['priceCode' => 'testCode']
                ],
                'createCount' => 1
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
