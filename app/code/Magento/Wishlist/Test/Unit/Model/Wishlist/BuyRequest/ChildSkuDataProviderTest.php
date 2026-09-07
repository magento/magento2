<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Wishlist\BuyRequest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Wishlist\BuyRequest\ChildSkuDataProvider;
use Magento\Wishlist\Model\Wishlist\Data\SelectedOption;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChildSkuDataProviderTest extends TestCase
{
    private const CONFIGURABLE_TYPE = 'configurable';

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ChildSkuDataProvider
     */
    private ChildSkuDataProvider $provider;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);

        $this->provider = new ChildSkuDataProvider(
            $this->productRepository
        );
    }

    public function testExecuteReturnsEmptyArrayWhenNoParentSku(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', null);

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayWhenParentSkuEqualsSku(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'same-sku', 'same-sku');

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayWhenSelectedOptionsPresent(): void
    {
        $selectedOption = $this->createMock(SelectedOption::class);
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku', null, null, [$selectedOption]);

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayOnNoSuchEntityException(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        $this->productRepository->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException());

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayWhenParentIsNotConfigurable(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        $childProduct = $this->createMock(Product::class);
        $parentProduct = $this->createMock(Product::class);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $parentProduct->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $parentProduct->expects($this->never())->method('getExtensionAttributes');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsSuperAttributeMapForValidChildSku(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');
        $productId = 42;

        $childProduct = $this->createMock(Product::class);
        $parentProduct = $this->createConfigurableParent(['93', '157']);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $childProduct->expects($this->once())
            ->method('getAttributes')
            ->willReturn([
                $this->createAttribute('93', 'color'),
                $this->createAttribute('157', 'size'),
            ]);

        $childProduct->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'color' => '56',
                    'size'  => '168',
                    default => null,
                };
            });

        $result = $this->provider->execute($wishlistItem, $productId);

        $this->assertSame(
            [
                'super_attribute' => [93 => '56', 157 => '168'],
                'product' => $productId,
            ],
            $result
        );
    }

    public function testExecuteReturnsSuperAttributeMapWithoutProductIdWhenProductIdIsNull(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        $childProduct = $this->createMock(Product::class);
        $parentProduct = $this->createConfigurableParent(['93']);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $childProduct->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$this->createAttribute('93', 'color')]);

        $childProduct->expects($this->once())
            ->method('getData')
            ->with('color')
            ->willReturn('56');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertArrayHasKey('super_attribute', $result);
        $this->assertArrayNotHasKey('product', $result);
        $this->assertSame([93 => '56'], $result['super_attribute']);
    }

    public function testExecuteSkipsAttributesWithEmptyValueOnChild(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        $childProduct = $this->createMock(Product::class);
        $parentProduct = $this->createConfigurableParent(['93', '157']);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $childProduct->expects($this->once())
            ->method('getAttributes')
            ->willReturn([
                $this->createAttribute('93', 'color'),
                $this->createAttribute('157', 'size'),
            ]);

        $childProduct->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'color' => null,
                    'size'  => '',
                    default => null,
                };
            });

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    /**
     * Build a configurable parent product mock exposing the given configurable axis attribute IDs.
     *
     * @param string[] $attributeIds
     * @return Product|MockObject
     */
    private function createConfigurableParent(array $attributeIds): Product
    {
        $parentProduct = $this->createMock(Product::class);
        $parentProduct->method('getTypeId')->willReturn(self::CONFIGURABLE_TYPE);

        $options = [];
        foreach ($attributeIds as $attributeId) {
            $option = $this->createMock(OptionInterface::class);
            $option->method('getAttributeId')->willReturn($attributeId);
            $options[] = $option;
        }

        $parentProduct->method('getExtensionAttributes')->willReturn(new ProductExtensionAttributesStub($options));

        return $parentProduct;
    }

    /**
     * Build an EAV attribute mock with the given ID and code.
     *
     * @param string $attributeId
     * @param string $attributeCode
     * @return AbstractAttribute|MockObject
     */
    private function createAttribute(string $attributeId, string $attributeCode): AbstractAttribute
    {
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeId', 'getAttributeCode'])
            ->getMock();
        $attribute->method('getAttributeId')->willReturn($attributeId);
        $attribute->method('getAttributeCode')->willReturn($attributeCode);

        return $attribute;
    }
}
