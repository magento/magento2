<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Model\SwatchAttributeCodes;
use Magento\Swatches\Model\SwatchAttributesProvider;

class SwatchAttributesProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SwatchAttributesProvider
     */
    private $swatchAttributeProvider;

    /**
     * @var Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeConfigurable;

    /**
     * @var SwatchAttributeCodes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $swatchAttributeCodes;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeHelperMock;

    protected function setUp()
    {
        $this->typeConfigurable = $this->createPartialMock(
            Configurable::class,
            ['getConfigurableAttributes', 'getCodes']
        );

        $this->swatchAttributeCodes = $this->createMock(SwatchAttributeCodes::class);

        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', 'getTypeId']);

        $this->attributeHelperMock = $this->createMock(\Magento\Swatches\Helper\Attribute::class);

        $this->swatchAttributeProvider = (new ObjectManager($this))->getObject(SwatchAttributesProvider::class, [
            'typeConfigurable' => $this->typeConfigurable,
            'swatchAttributeCodes' => $this->swatchAttributeCodes,
            'attributeHelper' => $this->attributeHelperMock
        ]);
    }

    public function dataForIsSwatchAttribute()
    {
        return [
            [
                [false]
            ],
            [
                [true]
            ],
            [
                [false, false]
            ],
            [
                [false, true]
            ],
            [
                [true, false]
            ],
            [
                [true, true]
            ]
        ];
    }

    /**
     * @dataProvider dataForIsSwatchAttribute
     */
    public function testProvide(array $dataForIsSwatchAttribute)
    {
        $this->productMock->method('getId')->willReturn(1);
        $this->productMock->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $configurableAttributes = [];
        $expected = [];

        // Test cases where swatchAttributeCodes::getCodes returns attribute codes which are no longer swatch attributes
        foreach ($dataForIsSwatchAttribute as $attributeId => $isSwatchAttribute) {
            $productAttributeMock = $this->createPartialMock(
                \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
                ['setData', 'hasData', 'getData']
            );

            $this->attributeHelperMock
                ->expects($this->at($attributeId))
                ->method('isSwatchAttribute')
                ->with($productAttributeMock)
                ->willReturn($isSwatchAttribute);

            $configAttributeMock = $this->createPartialMock(
                Configurable\Attribute::class,
                ['getAttributeId', 'getProductAttribute']
            );
            $configAttributeMock
                ->method('getAttributeId')
                ->willReturn($attributeId);
            $configAttributeMock
                ->method('getProductAttribute')
                ->willReturn($productAttributeMock);

            $configurableAttributes[] = $configAttributeMock;

            if ($isSwatchAttribute) {
                $expected[$attributeId] = $productAttributeMock;
            }
        }

        $this->typeConfigurable
            ->method('getConfigurableAttributes')
            ->with($this->productMock)
            ->willReturn($configurableAttributes);

        $this->swatchAttributeCodes
            ->method('getCodes')
            ->willReturn($dataForIsSwatchAttribute);

        $result = $this->swatchAttributeProvider->provide($this->productMock);

        $this->assertEquals($expected, $result);
    }
}
