<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Link;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link\Converter;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Api\ExtensionAttributesInterface;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    protected function setUp(): void
    {
        $this->converter = new Converter();
    }

    public function testConvertLinksToGroupedArray()
    {
        $linkedProductSku = 'linkedProductSample';
        $linkedProductId = '2016';
        $linkType = 'associated';
        $linkMock = $this->getMockBuilder(ProductLinkInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getData'])
            ->onlyMethods(['getLinkType', 'getLinkedProductSku', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $basicData = [$linkMock];
        $linkedProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $associatedProducts = [$linkedProductSku => $linkedProductMock];
        $info = [100, 300, 500];
        $infoFinal = [100, 300, 500, 'id' => $linkedProductId, 'qty' => 33];
        $linksAsArray = [$linkType => [$infoFinal]];

        $typeMock = $this->getMockBuilder(AbstractType::class)
            ->onlyMethods(['getAssociatedProducts'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getProductLinks')
            ->willReturn($basicData);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('getAssociatedProducts')
            ->with($productMock)
            ->willReturn($associatedProducts);
        $linkedProductMock->expects($this->once())
            ->method('getSku')
            ->willReturn($linkedProductSku);
        $linkMock->expects($this->once())
            ->method('getData')
            ->willReturn($info);
        $linkMock->expects($this->exactly(2))
            ->method('getLinkType')
            ->willReturn($linkType);
        $linkMock->expects($this->once())
            ->method('getLinkedProductSku')
            ->willReturn($linkedProductSku);
        $linkedProductMock->expects($this->once())
            ->method('getId')
            ->willReturn($linkedProductId);
        $attributeMock = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->addMethods(['__toArray'])
            ->getMockForAbstractClass();
        $linkMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('__toArray')
            ->willReturn(['qty' => 33]);

        $this->assertEquals($linksAsArray, $this->converter->convertLinksToGroupedArray($productMock));
    }
}
