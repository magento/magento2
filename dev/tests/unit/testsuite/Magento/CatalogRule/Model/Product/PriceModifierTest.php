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
        $this->ruleFactoryMock = $this->getMock('Magento\CatalogRule\Model\RuleFactory', array('create'));
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', array(), array(), '', false);
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
        return array('resulted_price_exists' => array(150, 150), 'resulted_price_not_exists' => array(null, 100));
    }

    public function testModifyPriceIfPriceNotExist()
    {
        $this->ruleFactoryMock->expects($this->never())->method('create');
        $this->assertEquals(null, $this->priceModifier->modifyPrice(null, $this->productMock));
    }
}
