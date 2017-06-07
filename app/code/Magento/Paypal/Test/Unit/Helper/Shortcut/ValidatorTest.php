<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Helper\Shortcut;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_paypalConfigFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_productTypeConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_paymentData;

    /** @var \Magento\Paypal\Helper\Shortcut\Validator */
    protected $helper;

    protected function setUp()
    {
        $this->_paypalConfigFactory = $this->getMock(
            \Magento\Paypal\Model\ConfigFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_productTypeConfig = $this->getMock(
            \Magento\Catalog\Model\ProductTypes\ConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $this->_registry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->_paymentData = $this->getMock(\Magento\Payment\Helper\Data::class, [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManager->getObject(
            \Magento\Paypal\Helper\Shortcut\Validator::class,
            [
                'paypalConfigFactory' => $this->_paypalConfigFactory,
                'registry' => $this->_registry,
                'productTypeConfig' => $this->_productTypeConfig,
                'paymentData' => $this->_paymentData
            ]
        );
    }

    /**
     * @dataProvider testIsContextAvailableDataProvider
     * @param bool $isVisible
     * @param bool $expected
     */
    public function testIsContextAvailable($isVisible, $expected)
    {
        $paypalConfig = $this->getMockBuilder(\Magento\Paypal\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paypalConfig->expects($this->any())
            ->method('getValue')
            ->with($this->stringContains('visible_on'))
            ->will($this->returnValue($isVisible));

        $this->_paypalConfigFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($paypalConfig));

        $this->assertEquals($expected, $this->helper->isContextAvailable('payment_code', true));
    }

    /**
     * @return array
     */
    public function testIsContextAvailableDataProvider()
    {
        return [
            [false, false],
            [true, true]
        ];
    }

    /**
     * @dataProvider testIsPriceOrSetAvailableDataProvider
     * @param bool $isInCatalog
     * @param double $productPrice
     * @param bool $isProductSet
     * @param bool $expected
     */
    public function testIsPriceOrSetAvailable($isInCatalog, $productPrice, $isProductSet, $expected)
    {
        $currentProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getFinalPrice', 'getTypeId', 'getTypeInstance'])
            ->getMock();
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $currentProduct->expects($this->any())->method('getFinalPrice')->will($this->returnValue($productPrice));
        $currentProduct->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $currentProduct->expects($this->any())->method('getTypeInstance')->will($this->returnValue($typeInstance));

        $this->_registry->expects($this->any())
            ->method('registry')
            ->with($this->equalTo('current_product'))
            ->will($this->returnValue($currentProduct));

        $this->_productTypeConfig->expects($this->any())
            ->method('isProductSet')
            ->will($this->returnValue($isProductSet));

        $typeInstance->expects($this->any())
            ->method('canConfigure')
            ->with($currentProduct)
            ->will($this->returnValue(false));

        $this->assertEquals($expected, $this->helper->isPriceOrSetAvailable($isInCatalog));
    }

    /**
     * @return array
     */
    public function testIsPriceOrSetAvailableDataProvider()
    {
        return [
            [false, 1, true, true],
            [false, null, null, true],
            [true, 0, false, false],
            [true, 10, false, true],
            [true, 0, true, true]
        ];
    }

    /**
     * @dataProvider testIsMethodAvailableDataProvider
     * @param bool $methodIsAvailable
     * @param bool $expected
     */
    public function testIsMethodAvailable($methodIsAvailable, $expected)
    {
        $methodInstance = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstance->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue($methodIsAvailable));

        $this->_paymentData->expects($this->any())
            ->method('getMethodInstance')
            ->will(
                $this->returnValue($methodInstance)
            );

        $this->assertEquals($expected, $this->helper->isMethodAvailable('payment_code'));
    }

    /**
     * @return array
     */
    public function testIsMethodAvailableDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
