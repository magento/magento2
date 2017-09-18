<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

class CartConfigurationTest extends \PHPUnit\Framework\TestCase
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
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
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
