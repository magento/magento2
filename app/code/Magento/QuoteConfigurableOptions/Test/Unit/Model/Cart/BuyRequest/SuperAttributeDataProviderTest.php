<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteConfigurableOptions\Test\Unit\Model\Cart\BuyRequest;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\Cart\Data\CartItem;
use Magento\Quote\Model\Cart\Data\SelectedOption;
use Magento\QuoteConfigurableOptions\Model\Cart\BuyRequest\SuperAttributeDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\QuoteConfigurableOptions\Model\Cart\BuyRequest\SuperAttributeDataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuperAttributeDataProviderTest extends TestCase
{
    use MockCreationTrait;

    private SuperAttributeDataProvider $provider;
    private ProductRepositoryInterface&MockObject $productRepository;
    private OptionCollection&MockObject $optionCollection;
    private MetadataPool&MockObject $metadataPool;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->optionCollection  = $this->createMock(OptionCollection::class);
        $this->metadataPool      = $this->createMock(MetadataPool::class);

        $this->provider = new SuperAttributeDataProvider(
            $this->productRepository,
            $this->optionCollection,
            $this->metadataPool,
        );
    }

    public function testExecuteResolvesFromSelectedOptions(): void
    {
        $uid = base64_encode('configurable/93/57');
        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $result = $this->provider->execute(new CartItem('simple-child', 1.0, null, [$selectedOption]));

        $this->assertSame(['super_attribute' => ['93' => '57']], $result);
    }

    public function testExecuteIgnoresNonConfigurableSelectedOptions(): void
    {
        $uid = base64_encode('custom-option/10/20');
        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $result = $this->provider->execute(new CartItem('simple-child', 1.0, null, [$selectedOption]));

        $this->assertSame(['super_attribute' => []], $result);
    }

    public function testExecuteFallsBackToParentSkuWhenNoSelectedOptions(): void
    {
        [$parentMock, $childMock] = $this->buildProductMocks(
            parentId: 10,
            childId: 42,
            configurableLinks: [42],
            linkFieldValue: 10,
            options: [['attribute_code' => 'color', 'attribute_id' => 93, 'values' => [['value_index' => 5]]]],
            childAttributeValue: 5
        );

        $this->productRepository->method('get')->willReturnMap([
            ['ParentItem',         false, null, false, $parentMock],
            ['ParentItem-Variant', false, null, false, $childMock],
        ]);

        $result = $this->provider->execute(new CartItem('ParentItem-Variant', 1.0, 'ParentItem'));

        $this->assertSame(['super_attribute' => [93 => 5]], $result);
    }

    public function testSelectedOptionsTakePriorityOverParentSku(): void
    {
        $uid = base64_encode('configurable/93/57');
        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute(new CartItem('ParentItem-Variant', 1.0, 'ParentItem', [$selectedOption]));

        $this->assertSame(['super_attribute' => ['93' => '57']], $result);
    }

    public function testExecuteReturnsEmptyWhenNoSelectedOptionsAndNoParentSku(): void
    {
        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute(new CartItem('simple-standalone', 1.0));

        $this->assertSame(['super_attribute' => []], $result);
    }

    public function testExecuteThrowsWhenChildIsNotVariantOfParent(): void
    {
        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getConfigurableProductLinks']
        );
        $extensionAttributes->method('getConfigurableProductLinks')->willReturn([99]);

        $parentMock = $this->createMock(Product::class);
        $parentMock->method('getId')->willReturn(10);
        $parentMock->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $parentMock->method('getData')->willReturn(10);

        $childMock = $this->createMock(Product::class);
        $childMock->method('getId')->willReturn(42);

        $this->productRepository->method('get')->willReturnMap([
            ['ParentItem',   false, null, false, $parentMock],
            ['other-simple', false, null, false, $childMock],
        ]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('not a variant');

        $this->provider->execute(new CartItem('other-simple', 1.0, 'ParentItem'));
    }

    public function testExecuteThrowsWhenProductNotFound(): void
    {
        $this->productRepository->method('get')->willThrowException(new NoSuchEntityException());

        $this->expectException(LocalizedException::class);

        $this->provider->execute(new CartItem('ParentItem-Variant', 1.0, 'NonExistentParent'));
    }

    public function testExecuteThrowsOnMalformedSelectedOptionUid(): void
    {
        $uid = base64_encode('configurable/only-two-parts');
        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $this->expectException(LocalizedException::class);

        $this->provider->execute(new CartItem('simple-child', 1.0, null, [$selectedOption]));
    }

    private function buildProductMocks(
        int $parentId,
        int $childId,
        array $configurableLinks,
        int $linkFieldValue,
        array $options,
        mixed $childAttributeValue,
    ): array {
        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getConfigurableProductLinks']
        );
        $extensionAttributes->method('getConfigurableProductLinks')->willReturn($configurableLinks);

        $parentMock = $this->createMock(Product::class);
        $parentMock->method('getId')->willReturn($parentId);
        $parentMock->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $parentMock->method('getData')->willReturn($linkFieldValue);

        $childMock = $this->createMock(Product::class);
        $childMock->method('getId')->willReturn($childId);
        $childMock->method('getData')->willReturn($childAttributeValue);

        $productMetadata = $this->createMock(EntityMetadataInterface::class);
        $productMetadata->method('getLinkField')->willReturn('entity_id');
        $this->metadataPool->method('getMetadata')->with(ProductInterface::class)->willReturn($productMetadata);

        $this->optionCollection->method('getAttributesByProductId')->with($linkFieldValue)->willReturn($options);

        return [$parentMock, $childMock];
    }
}
