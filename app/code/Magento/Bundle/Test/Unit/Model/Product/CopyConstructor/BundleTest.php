<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product\CopyConstructor;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Bundle\Model\Link;
use Magento\Bundle\Model\Product\CopyConstructor\Bundle;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Bundle
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Bundle::class);
    }

    public function testBuildNegative()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $duplicate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('other product type');
        $this->model->build($product, $duplicate);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildPositive()
    {
        /** @var Product|\PHPUnit\Framework\MockObject\MockObject $product */
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributesProduct = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getBundleProductOptions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesProduct);

        $productLink = $this->getMockBuilder(Link::class)
            ->setMethods(['setSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productLink->expects($this->exactly(2))
            ->method('setSelectionId')
            ->with($this->identicalTo(null));
        $firstOption = $this->getMockBuilder(BundleOptionInterface::class)
            ->setMethods(['getProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $firstOption->expects($this->once())
            ->method('getProductLinks')
            ->willReturn([$productLink]);
        $firstOption->expects($this->once())
            ->method('setOptionId')
            ->with($this->identicalTo(null));
        $secondOption = $this->getMockBuilder(BundleOptionInterface::class)
            ->setMethods(['getProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $secondOption->expects($this->once())
            ->method('getProductLinks')
            ->willReturn([$productLink]);
        $secondOption->expects($this->once())
            ->method('setOptionId')
            ->with($this->identicalTo(null));
        $bundleOptions = [
            $firstOption,
            $secondOption
        ];
        $extensionAttributesProduct->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn($bundleOptions);

        /** @var Product|\PHPUnit\Framework\MockObject\MockObject $duplicate */
        $duplicate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributesDuplicate = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['setBundleProductOptions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $duplicate->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesDuplicate);
        $extensionAttributesDuplicate->expects($this->once())
            ->method('setBundleProductOptions')
            ->withConsecutive([$bundleOptions]);

        $this->model->build($product, $duplicate);
    }

    /**
     * @return void
     */
    public function testBuildWithoutOptions()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributesProduct = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getBundleProductOptions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesProduct);

        $extensionAttributesProduct->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn(null);

        $duplicate = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributesDuplicate = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['setBundleProductOptions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $duplicate->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesDuplicate);
        $extensionAttributesDuplicate->expects($this->once())
            ->method('setBundleProductOptions')
            ->with([]);

        $this->model->build($product, $duplicate);
    }
}
