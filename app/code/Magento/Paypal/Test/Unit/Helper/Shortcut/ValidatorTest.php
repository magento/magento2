<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Helper\Shortcut;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Helper\Shortcut\Validator;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var MockObject */
    protected $_paypalConfigFactory;

    /** @var MockObject */
    protected $_registry;

    /** @var MockObject */
    protected $_productTypeConfig;

    /** @var MockObject */
    protected $_paymentData;

    /** @var Validator */
    protected $helper;

    protected function setUp(): void
    {
        $this->_paypalConfigFactory = $this->createPartialMock(ConfigFactory::class, ['create']);
        $this->_productTypeConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->_registry = $this->createMock(Registry::class);
        $this->_paymentData = $this->createMock(Data::class);

        $objectManager = new ObjectManager($this);
        $this->helper = $objectManager->getObject(
            Validator::class,
            [
                'paypalConfigFactory' => $this->_paypalConfigFactory,
                'registry' => $this->_registry,
                'productTypeConfig' => $this->_productTypeConfig,
                'paymentData' => $this->_paymentData
            ]
        );
    }

    /**
     * @dataProvider isContextAvailableDataProvider
     * @param bool $isVisible
     * @param bool $expected
     */
    public function testIsContextAvailable($isVisible, $expected)
    {
        $paypalConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paypalConfig->expects($this->any())
            ->method('getValue')
            ->with($this->stringContains('visible_on'))
            ->willReturn($isVisible);

        $this->_paypalConfigFactory->expects($this->any())
            ->method('create')
            ->willReturn($paypalConfig);

        $this->assertEquals($expected, $this->helper->isContextAvailable('payment_code', true));
    }

    /**
     * @return array
     */
    public static function isContextAvailableDataProvider()
    {
        return [
            [false, false],
            [true, true]
        ];
    }

    /**
     * @dataProvider isPriceOrSetAvailableDataProvider
     * @param bool $isInCatalog
     * @param double $productPrice
     * @param bool $isProductSet
     * @param bool $expected
     */
    public function testIsPriceOrSetAvailable($isInCatalog, $productPrice, $isProductSet, $expected)
    {
        $currentProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__wakeup', 'getFinalPrice', 'getTypeId', 'getTypeInstance'])
            ->getMock();
        $typeInstance = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canConfigure'])
            ->getMockForAbstractClass();
        $currentProduct->expects($this->any())->method('getFinalPrice')->willReturn($productPrice);
        $currentProduct->expects($this->any())->method('getTypeId')->willReturn('simple');
        $currentProduct->expects($this->any())->method('getTypeInstance')->willReturn($typeInstance);

        $this->_registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->willReturn($currentProduct);

        $this->_productTypeConfig->expects($this->any())
            ->method('isProductSet')
            ->willReturn($isProductSet);

        $typeInstance->expects($this->any())
            ->method('canConfigure')
            ->with($currentProduct)
            ->willReturn(false);

        $this->assertEquals($expected, $this->helper->isPriceOrSetAvailable($isInCatalog));
    }

    /**
     * @return array
     */
    public static function isPriceOrSetAvailableDataProvider()
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
     * @dataProvider isMethodAvailableDataProvider
     * @param bool $methodIsAvailable
     * @param bool $expected
     */
    public function testIsMethodAvailable($methodIsAvailable, $expected)
    {
        $methodInstance = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstance->expects($this->any())
            ->method('isAvailable')
            ->willReturn($methodIsAvailable);

        $this->_paymentData->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn(
                $methodInstance
            );

        $this->assertEquals($expected, $this->helper->isMethodAvailable('payment_code'));
    }

    /**
     * @return array
     */
    public static function isMethodAvailableDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
