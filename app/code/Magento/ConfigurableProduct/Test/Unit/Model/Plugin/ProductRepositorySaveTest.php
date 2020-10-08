<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Plugin\ProductRepositorySave;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductExtensionAttributes;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\InputException;

/**
 * Test for ProductRepositorySave plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositorySaveTest extends TestCase
{
    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepository;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Product|MockObject
     */
    private $result;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ProductExtensionAttributes|MockObject
     */
    private $extensionAttributes;

    /**
     * @var ProductAttributeInterface|MockObject
     */
    private $eavAttribute;

    /**
     * @var OptionInterface|MockObject
     */
    private $option;

    /**
     * @var ProductRepositorySave
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productAttributeRepository =
            $this->getMockForAbstractClass(ProductAttributeRepositoryInterface::class);

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $this->result = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMock();

        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);

        $this->extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->getMockForAbstractClass();

        $this->eavAttribute = $this->getMockForAbstractClass(ProductAttributeInterface::class);

        $this->option = $this->getMockForAbstractClass(OptionInterface::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            ProductRepositorySave::class,
            [
                'productAttributeRepository' => $this->productAttributeRepository,
                'productRepository' => $this->productRepository
            ]
        );
    }

    /**
     * Validating the result after saving a configurable product
     *
     * @return void
     */
    public function testBeforeSaveWhenProductIsSimple(): void
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');
        $this->product->expects(static::never())
            ->method('getExtensionAttributes');

        $this->assertEquals(
            $this->product,
            $this->plugin->beforeSave($this->productRepository, $this->product)[0]
        );
    }

    /**
     * Test saving a configurable product without attribute options
     *
     * @return void
     */
    public function testBeforeSaveWithoutOptions(): void
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);

        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([]);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn([]);

        $this->productAttributeRepository->expects(static::never())
            ->method('get');

        $this->assertEquals(
            $this->product,
            $this->plugin->beforeSave($this->productRepository, $this->product)[0]
        );
    }

    /**
     * Test saving a configurable product with same set of attribute values
     *
     * @return void
     */
    public function testBeforeSaveWithLinks(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Products "5" and "4" have the same set of attribute values.');
        $links = [4, 5];
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->productAttributeRepository->expects(static::never())
            ->method('get');

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->productRepository->expects(static::exactly(2))
            ->method('getById')
            ->willReturn($product);

        $product->expects(static::never())
            ->method('getData');

        $this->plugin->beforeSave($this->productRepository, $this->product);
    }

    /**
     * Test saving a configurable product with missing attribute
     *
     * @return void
     */
    public function testBeforeSaveWithLinksWithMissingAttribute(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Product with id "4" does not contain required attribute "color".');
        $simpleProductId = 4;
        $links = [$simpleProductId, 5];
        $attributeCode = 'color';
        $attributeId = 23;

        $this->option->expects(static::once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$this->option]);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->willReturn($this->eavAttribute);

        $this->eavAttribute->expects(static::once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->productRepository->expects(static::once())
            ->method('getById')
            ->willReturn($product);

        $product->expects(static::once())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn(null);

        $this->plugin->beforeSave($this->productRepository, $this->product);
    }

    /**
     * Test saving a configurable product with duplicate attributes
     *
     * @return void
     */
    public function testBeforeSaveWithLinksWithDuplicateAttributes(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Products "5" and "4" have the same set of attribute values.');
        $links = [4, 5];
        $attributeCode = 'color';
        $attributeId = 23;

        $this->option->expects(static::once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$this->option]);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->willReturn($this->eavAttribute);

        $this->eavAttribute->expects(static::once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->productRepository->expects(static::exactly(2))
            ->method('getById')
            ->willReturn($product);

        $product->expects(static::exactly(4))
            ->method('getData')
            ->with($attributeCode)
            ->willReturn($attributeId);

        $this->plugin->beforeSave($this->productRepository, $this->product);
    }
}
