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
namespace Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->registryMock = $this->getMock('\Magento\Framework\Registry', array(), array(), '', false);
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->coreHelperMock = $this->getMock('\Magento\Core\Helper\Data', array(), array(), '', false);
        $this->storeManagerMock = $this->getMock(
            '\Magento\Framework\StoreManagerInterface',
            array(),
            array(),
            '',
            false
        );

        $customerMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Customer'
        )->disableOriginalConstructor()->getMock();
        $customerMock->expects($this->any())->method('getId')->will($this->returnValue(1));

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $objectHelper->getObject(
            'Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped',
            array(
                'registry' => $this->registryMock,
                'storeManager' => $this->storeManagerMock,
                'coreHelper' => $this->coreHelperMock,
                'data' => array('product' => $this->productMock)
            )
        );
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getProduct
     */
    public function testGetProductPositive()
    {
        $instanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );
        $storeMock = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects($this->once())->method('getStoreFilter')->will($this->returnValue($storeMock));

        $instanceMock->expects($this->never())->method('setStoreFilter');

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getProduct
     */
    public function testGetProductNegative()
    {
        $storeId = 2;
        $instanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );
        $storeMock = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects(
            $this->once()
        )->method(
            'getStoreFilter'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(null)
        );

        $this->productMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));

        $this->storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->will(
            $this->returnValue($storeMock)
        );

        $instanceMock->expects($this->once())->method('setStoreFilter')->with($storeMock, $this->productMock);

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getAssociatedProducts
     */
    public function testGetAssociatedProducts()
    {
        $storeId = 2;

        $instanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $associatedProduct = clone $this->productMock;

        $associatedProduct->expects($this->once())->method('setStoreId')->with($storeId);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(array($associatedProduct))
        );

        $this->productMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));

        $this->assertEquals(array($associatedProduct), $this->block->getAssociatedProducts());
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::setPreconfiguredValue
     */
    public function testSetPreconfiguredValue()
    {
        $storeId = 2;

        $objectMock = $this->getMock('\Magento\Framework\Object', array('getSuperGroup'), array(), '', false);
        $instanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );

        $objectMock->expects($this->once())->method('getSuperGroup')->will($this->returnValue(array()));

        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->will(
            $this->returnValue($objectMock)
        );

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $associatedProduct = clone $this->productMock;

        $associatedProduct->expects($this->once())->method('setStoreId')->with($storeId);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(array($associatedProduct))
        );

        $this->productMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));

        $this->assertEquals($this->block, $this->block->setPreconfiguredValue());
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getCanShowProductPrice
     */
    public function testGetCanShowProductPrice()
    {
        $this->assertEquals(true, $this->block->getCanShowProductPrice($this->productMock));
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getIsLastFieldset
     */
    public function testGetIsLastFieldsetPositive()
    {
        $this->block->setData('is_last_fieldset', true);

        $this->productMock->expects($this->never())->method('getOptions');

        $this->assertEquals(true, $this->block->getIsLastFieldset());
    }

    /**
     * @param array|bool $options
     * @param bool $expectedResult
     *
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getIsLastFieldset
     * @dataProvider getIsLastFieldsetDataProvider
     */
    public function testGetIsLastFieldsetNegative($options, $expectedResult)
    {
        $instanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );

        $this->block->setData('is_last_fieldset', false);

        $this->productMock->expects($this->once())->method('getOptions')->will($this->returnValue($options));

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects($this->once())->method('getStoreFilter')->will($this->returnValue(true));

        $this->assertEquals($expectedResult, $this->block->getIsLastFieldset());
    }

    /**
     * Data provider for testGetIsLastFieldsetNegative
     *
     * @return array
     */
    public function getIsLastFieldsetDataProvider()
    {
        return array(
            'case1' => array('options' => false, 'expectedResult' => true),
            'case2' => array('options' => array('option'), 'expectedResult' => false)
        );
    }

    /**
     * @covers Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getCurrencyPrice
     */
    public function testGetCurrencyPrice()
    {
        $storeId = 2;
        $price = 1.22;
        $expectedPrice = 1;

        $instanceMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
            '',
            false
        );

        $this->productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instanceMock));

        $instanceMock->expects($this->once())->method('getStoreFilter')->will($this->returnValue(true));

        $this->productMock->expects($this->once())->method('getStore')->will($this->returnValue($storeId));

        $this->coreHelperMock->expects(
            $this->once()
        )->method(
            'currencyByStore'
        )->with(
            $price,
            $storeId,
            false
        )->will(
            $this->returnValue($expectedPrice)
        );

        $this->assertEquals($expectedPrice, $this->block->getCurrencyPrice($price));
    }
}
