<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute\Interceptor;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Swatches\Model\SwatchAttributeCodes;
use Magento\Swatches\Model\SwatchAttributesProvider;

class SwatchAttributesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwatchAttributesProvider
     */
    protected $swatchAttributesProvider;

    /**
     * @var Configurable
     */
    private $typeConfigurableMock;

    /**
     * @var SwatchAttributeCodes
     */
    private $swatchAttributeCodesMock;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productMock;

    protected function setUp()
    {
        $this->typeConfigurableMock = $this->getMock(
            Configurable::class,
            ['getConfigurableAttributes', 'getCodes'],
            [],
            '',
            false
        );

        $this->swatchAttributeCodesMock = $this->getMock(
            SwatchAttributeCodes::class,
            [],
            [],
            '',
            false
        );

        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'getTypeId'],
            [],
            '',
            false
        );

        $this->swatchAttributeProvider = new SwatchAttributesProvider(
            $this->typeConfigurableMock,
            $this->swatchAttributeCodesMock
        );
    }

    public function testProvide()
    {
        $this->productMock->method('getId')->willReturn(1);
        $this->productMock->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $productAttributeMock = $this->getMock(
            Interceptor::class,
            [],
            [],
            '',
            false
        );

        $configAttributeMock = $this->getMock(
            Configurable\Attribute::class,
            ['getAttributeId', 'getProductAttribute'],
            [],
            '',
            false
        );
        $configAttributeMock
            ->method('getAttributeId')
            ->willReturn(1);

        $configAttributeMock
            ->method('getProductAttribute')
            ->willReturn($productAttributeMock);

        $this->typeConfigurableMock
            ->method('getConfigurableAttributes')
            ->with($this->productMock)
            ->willReturn([$configAttributeMock]);

        $swatchAttributes = [1 => 'text_swatch'];

        $this->swatchAttributeCodesMock
            ->method('getCodes')
            ->willReturn($swatchAttributes);

        $expected = [1 => $productAttributeMock];

        $result = $this->swatchAttributeProvider->provide($this->productMock);

        $this->assertEquals($expected, $result);
    }
}
