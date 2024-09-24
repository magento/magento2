<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Model\SwatchAttributeCodes;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Swatches\Model\SwatchAttributeType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwatchAttributesProviderTest extends TestCase
{
    /**
     * @var SwatchAttributesProvider
     */
    private $swatchAttributeProvider;

    /**
     * @var Configurable|MockObject
     */
    private $typeConfigurable;

    /**
     * @var SwatchAttributeCodes|MockObject
     */
    private $swatchAttributeCodes;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var SwatchAttributeType|MockObject
     */
    private $swatchTypeChecker;

    protected function setUp(): void
    {
        $this->typeConfigurable = $this->getMockBuilder(Configurable::class)
            ->addMethods(['getCodes', 'getProductAttribute'])
            ->onlyMethods(['getConfigurableAttributes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->swatchAttributeCodes = $this->createMock(SwatchAttributeCodes::class);

        $this->productMock = $this->createPartialMock(Product::class, ['getId', 'getTypeId']);
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

        $attributeMock =  $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['setStoreId'])
            ->onlyMethods(['getData', 'setData', 'getSource', 'hasData'])
            ->getMock();

        $configAttributeMock = $this->getMockBuilder(Configurable\Attribute::class)->addMethods(['getProductAttribute'])
            ->onlyMethods(['getAttributeId'])
            ->disableOriginalConstructor()
            ->getMock();
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
