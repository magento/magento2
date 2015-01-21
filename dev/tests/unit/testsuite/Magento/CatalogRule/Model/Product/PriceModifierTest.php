<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Product;

class PriceModifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Product\PriceModifier
     */
    protected $priceModifier;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleMock;

    protected function setUp()
    {
        $this->ruleFactoryMock = $this->getMock(
            'Magento\CatalogRule\Model\RuleFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->priceModifier = new \Magento\CatalogRule\Model\Product\PriceModifier($this->ruleFactoryMock);
    }

    /**
     * @param int|null $resultPrice
     * @param int $expectedPrice
     * @dataProvider modifyPriceDataProvider
     */
    public function testModifyPriceIfPriceExists($resultPrice, $expectedPrice)
    {
        $this->ruleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->ruleMock));
        $this->ruleMock->expects(
            $this->once()
        )->method(
            'calcProductPriceRule'
        )->with(
            $this->productMock,
            100
        )->will(
            $this->returnValue($resultPrice)
        );
        $this->assertEquals($expectedPrice, $this->priceModifier->modifyPrice(100, $this->productMock));
    }

    public function modifyPriceDataProvider()
    {
        return ['resulted_price_exists' => [150, 150], 'resulted_price_not_exists' => [null, 100]];
    }

    public function testModifyPriceIfPriceNotExist()
    {
        $this->ruleFactoryMock->expects($this->never())->method('create');
        $this->assertEquals(null, $this->priceModifier->modifyPrice(null, $this->productMock));
    }
}
