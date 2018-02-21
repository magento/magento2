<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Link;

use Magento\Catalog\Model\Product\Link\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new Converter();
    }

    public function testConvertLinksToGroupedArray()
    {
        $linkedProductSku = 'linkedProductSample';
        $linkedProductId = '2016';
        $linkType = 'associated';
        $linkMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductLinkInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getLinkType', 'getLinkedProductSku', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $basicData = [$linkMock];
        $linkedProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $associatedProducts = [$linkedProductSku => $linkedProductMock];
        $info = [100, 300, 500];
        $infoFinal = [100, 300, 500, 'id' => $linkedProductId, 'qty' => 33];
        $linksAsArray = [$linkType => [$infoFinal]];

        $typeMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->setMethods(['getAssociatedProducts'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
        $attributeMock = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesInterface::class)
            ->setMethods(['__toArray'])
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
