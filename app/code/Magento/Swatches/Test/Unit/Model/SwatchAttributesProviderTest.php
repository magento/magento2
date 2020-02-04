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
use Magento\Swatches\Model\SwatchAttributeType;

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
     * @var SwatchAttributeType | \PHPUnit_Framework_MockObject_MockObject
     */
    private $swatchTypeChecker;

    protected function setUp()
    {
        $this->typeConfigurable = $this->createPartialMock(
            Configurable::class,
            ['getConfigurableAttributes', 'getCodes', 'getProductAttribute']
        );

        $this->swatchAttributeCodes = $this->createMock(SwatchAttributeCodes::class);

        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', 'getTypeId']);
        $this->swatchTypeChecker = $this->createMock(SwatchAttributeType::class);

        $this->swatchAttributeProvider = (new ObjectManager($this))->getObject(SwatchAttributesProvider::class, [
            'typeConfigurable' => $this->typeConfigurable,
            'swatchAttributeCodes' => $this->swatchAttributeCodes,
            'swatchTypeChecker' => $this->swatchTypeChecker,
        ]);
    }

    public function testProvide()
    {
        $this->productMock->method('getId')->willReturn(1);
        $this->productMock->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $attributeMock =  $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'getData', 'setData', 'getSource', 'hasData'])
            ->getMock();

        $configAttributeMock = $this->createPartialMock(
            Configurable\Attribute::class,
            ['getAttributeId', 'getProductAttribute']
        );
        $configAttributeMock
            ->method('getAttributeId')
            ->willReturn(1);

        $configAttributeMock
            ->method('getProductAttribute')
            ->willReturn($attributeMock);

        $this->typeConfigurable
            ->method('getConfigurableAttributes')
            ->with($this->productMock)
            ->willReturn([$configAttributeMock]);

        $swatchAttributes = [1 => 'text_swatch'];
        $this->swatchAttributeCodes
            ->method('getCodes')
            ->willReturn($swatchAttributes);

        $this->swatchTypeChecker->expects($this->once())->method('isSwatchAttribute')->willReturn(true);

        $result = $this->swatchAttributeProvider->provide($this->productMock);

        $this->assertEquals([1 => $attributeMock], $result);
    }
}
