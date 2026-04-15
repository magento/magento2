<?php
/**
 * Copyright 2020 Adobe
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

    // -------------------------------------------------------------------------
    // selected_options path (existing behaviour)
    // -------------------------------------------------------------------------

    /**
     * When selected_options contain a valid configurable UID, super_attribute is built from them.
     */
    public function testExecuteResolvesFromSelectedOptions(): void
    {
        // configurable/93/57  -> attributeId=93, valueIndex=57
        $uid = base64_encode('configurable/93/57');

        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $cartItem = new CartItem('simple-child', 1.0, null, [$selectedOption]);

        $result = $this->provider->execute($cartItem);

        $this->assertSame(['super_attribute' => ['93' => '57']], $result);
    }

    /**
     * Non-configurable selected_options (e.g. custom-options) are ignored.
     */
    public function testExecuteIgnoresNonConfigurableSelectedOptions(): void
    {
        $uid = base64_encode('custom-option/10/20');

        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $cartItem = new CartItem('simple-child', 1.0, null, [$selectedOption]);

        $result = $this->provider->execute($cartItem);

        $this->assertSame(['super_attribute' => []], $result);
    }

    /**
     * When no selected_options are provided but parent_sku is set, the provider
     * falls back to resolving super_attribute via parent_sku + child sku (GH-40598).
     */
    public function testExecuteFallsBackToParentSkuWhenNoSelectedOptions(): void
    {
        $cartItem = new CartItem('ParentItem-Variant', 1.0, 'ParentItem');

        [$parentMock, $childMock] = $this->buildProductMocks(
            parentId: 10,
            childId: 42,
            configurableLinks: [42],
            linkFieldValue: 10,
            options: [
                [
                    'attribute_code' => 'color',
                    'attribute_id'   => 93,
                    'values'         => [['value_index' => 5]],
                ],
            ],
            childAttributeValue: 5
        );

        $this->productRepository->method('get')->willReturnMap([
            ['ParentItem',         false, null, false, $parentMock],
            ['ParentItem-Variant', false, null, false, $childMock],
        ]);

        $result = $this->provider->execute($cartItem);

        $this->assertSame(['super_attribute' => [93 => 5]], $result);
    }

    /**
     * selected_options take priority over parent_sku: if configurable UIDs are present
     * the parent_sku fallback must NOT be triggered.
     */
    public function testSelectedOptionsTakePriorityOverParentSku(): void
    {
        $uid = base64_encode('configurable/93/57');

        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $cartItem = new CartItem('ParentItem-Variant', 1.0, 'ParentItem', [$selectedOption]);

        // Repository should never be called when selected_options already contain the data
        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($cartItem);

        $this->assertSame(['super_attribute' => ['93' => '57']], $result);
    }

    /**
     * GH-40598: When neither selected_options nor parent_sku are provided, the provider
     * returns an empty super_attribute array (simple product behaviour unchanged).
     */
    public function testExecuteReturnsEmptyWhenNoSelectedOptionsAndNoParentSku(): void
    {
        $cartItem = new CartItem('simple-standalone', 1.0);

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($cartItem);

        $this->assertSame(['super_attribute' => []], $result);
    }

    /**
     * GH-40598: A LocalizedException is thrown when the child SKU is not a variant
     * of the supplied parent_sku.
     */
    public function testExecuteThrowsWhenChildIsNotVariantOfParent(): void
    {
        $cartItem = new CartItem('other-simple', 1.0, 'ParentItem');

        // getConfigurableProductLinks() is a generated extension attribute method —
        // we must use createPartialMockWithReflection to be able to stub it.
        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getConfigurableProductLinks']
        );
        $extensionAttributes->method('getConfigurableProductLinks')->willReturn([99]); // child id 42 not in list

        $parentMock = $this->createMock(Product::class);
        $parentMock->method('getId')->willReturn(10);
        $parentMock->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $parentMock->method('getData')->willReturn(10);

        $childMock = $this->createMock(Product::class);
        $childMock->method('getId')->willReturn(42); // 42 != 99

        $this->productRepository->method('get')->willReturnMap([
            ['ParentItem',   false, null, false, $parentMock],
            ['other-simple', false, null, false, $childMock],
        ]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('not a variant');

        $this->provider->execute($cartItem);
    }

    /**
     * GH-40598: A LocalizedException is thrown when the parent or child product does not exist.
     */
    public function testExecuteThrowsWhenProductNotFound(): void
    {
        $cartItem = new CartItem('ParentItem-Variant', 1.0, 'NonExistentParent');

        $this->productRepository->method('get')
            ->willThrowException(new NoSuchEntityException());

        $this->expectException(LocalizedException::class);

        $this->provider->execute($cartItem);
    }

    /**
     * GH-40598: A LocalizedException is thrown when a selected_option UID has wrong format.
     */
    public function testExecuteThrowsOnMalformedSelectedOptionUid(): void
    {
        $uid = base64_encode('configurable/only-two-parts');  // should be 3 parts

        $selectedOption = $this->createMock(SelectedOption::class);
        $selectedOption->method('getId')->willReturn($uid);

        $cartItem = new CartItem('simple-child', 1.0, null, [$selectedOption]);

        $this->expectException(LocalizedException::class);

        $this->provider->execute($cartItem);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build parent & child product mocks for the parent_sku fallback path.
     *
     * getConfigurableProductLinks() is a generated extension attribute method —
     * we must use createPartialMockWithReflection to be able to stub it.
     *
     * @param int   $parentId
     * @param int   $childId
     * @param int[] $configurableLinks
     * @param int   $linkFieldValue
     * @param array $options
     * @param mixed $childAttributeValue
     * @return array{0: MockObject, 1: MockObject}
     */
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
        $this->metadataPool->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($productMetadata);

        $this->optionCollection->method('getAttributesByProductId')
            ->with($linkFieldValue)
            ->willReturn($options);

        return [$parentMock, $childMock];
    }
}
