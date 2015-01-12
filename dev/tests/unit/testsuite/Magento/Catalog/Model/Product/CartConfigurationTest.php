<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

class CartConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $productType
     * @param array $config
     * @param boolean $expected
     * @dataProvider isProductConfiguredDataProvider
     */
    public function testIsProductConfigured($productType, $config, $expected)
    {
        $cartConfiguration = new \Magento\Catalog\Model\Product\CartConfiguration();
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($productType));
        $this->assertEquals($expected, $cartConfiguration->isProductConfigured($productMock, $config));
    }

    public function isProductConfiguredDataProvider()
    {
        return [
            'simple' => ['simple', [], false],
            'virtual' => ['virtual', ['options' => true], true],
            'bundle' => ['bundle', ['bundle_option' => 'option1'], true],
            'some_option_type' => ['some_option_type', [], false]
        ];
    }
}
