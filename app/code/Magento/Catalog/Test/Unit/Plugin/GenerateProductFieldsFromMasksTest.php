<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Plugin\GenerateProductFieldsFromMasks;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenerateProductFieldsFromMasksTest extends TestCase
{
    /**
     * @param string $mask
     * @param array $productData
     * @param string|null $expected
     * @return void
     */
    #[DataProvider('maskDataProvider')]
    public function testMaskIsApplied(string $mask, array $productData, ?string $expected): void
    {
        $product = $this->createProduct($productData);
        $product->expects($expected === null ? $this->never() : $this->once())
            ->method('setCustomAttribute')
            ->with('meta_title', $expected);

        $this->createPlugin(['meta_title' => $mask], false)
            ->beforeSave($this->createStub(ProductRepositoryInterface::class), $product);
    }

    /**
     * @return array
     */
    public static function maskDataProvider(): array
    {
        return [
            'placeholders' => ['{{name}} | {{sku}}', ['name' => 'A', 'sku' => 'b'], 'A | b'],
            'repeated placeholder' => ['{{name}} {{name}}', ['name' => 'A'], 'A A'],
            'html is stripped' => ['{{description}}', ['description' => '<p>A <b>b</b></p>'], 'A b'],
            'style is removed' => ['{{description}}', ['description' => '<style>p{}</style><p>A</p>'], 'A'],
            'widget is removed' => ['{{description}}', ['description' => 'A{{widget type="x"}}'], 'A'],
            'empty placeholder' => ['{{name}} {{description}}', ['name' => 'A'], 'A '],
            'no value for any placeholder' => ['{{description}} text', [], null],
            'mask without placeholders' => ['text', ['name' => 'A'], null],
        ];
    }

    /**
     * @return void
     */
    public function testValueFromRequestIsKept(): void
    {
        $product = $this->createProduct(['name' => 'A', 'meta_title' => '']);
        $product->expects($this->never())->method('setCustomAttribute');

        $this->createPlugin(['meta_title' => '{{name}}'], false)
            ->beforeSave($this->createStub(ProductRepositoryInterface::class), $product);
    }

    /**
     * @return void
     */
    public function testExistingProductIsSkipped(): void
    {
        $product = $this->createProduct(['name' => 'A', 'sku' => 'b']);
        $product->expects($this->never())->method('setCustomAttribute');

        $this->createPlugin(['meta_title' => '{{name}}'], true)
            ->beforeSave($this->createStub(ProductRepositoryInterface::class), $product);
    }

    /**
     * @return void
     */
    public function testProductWithIdIsSkipped(): void
    {
        $product = $this->createProduct(['name' => 'A'], 5);
        $product->expects($this->never())->method('setCustomAttribute');

        $this->createPlugin(['meta_title' => '{{name}}'], false)
            ->beforeSave($this->createStub(ProductRepositoryInterface::class), $product);
    }

    /**
     * A PCRE failure on a long description must not break the product save.
     *
     * @return void
     */
    public function testRegexFailureLeavesFieldEmpty(): void
    {
        $product = $this->createProduct(['description' => '<style>' . str_repeat('a', 1000)]);
        $product->expects($this->never())->method('setCustomAttribute');
        $jit = ini_set('pcre.jit', '0');
        // Enough for the short mask, too low for the long description.
        $backtrackLimit = ini_set('pcre.backtrack_limit', '100');

        try {
            $this->createPlugin(['meta_title' => '{{description}}'], false)
                ->beforeSave($this->createStub(ProductRepositoryInterface::class), $product);
        } finally {
            ini_set('pcre.jit', (string)$jit);
            ini_set('pcre.backtrack_limit', (string)$backtrackLimit);
        }
    }

    /**
     * @return void
     */
    public function testDisabledSettingLeavesProductUntouched(): void
    {
        $product = $this->createProduct(['name' => 'A', 'sku' => 'b']);
        $product->expects($this->never())->method('setCustomAttribute');

        $this->createPlugin(['meta_title' => '{{name}}'], false, false)
            ->beforeSave($this->createStub(ProductRepositoryInterface::class), $product);
    }

    /**
     * @param array $masks
     * @param bool $productExists
     * @param bool $enabled
     * @return GenerateProductFieldsFromMasks
     */
    private function createPlugin(
        array $masks,
        bool $productExists,
        bool $enabled = true
    ): GenerateProductFieldsFromMasks {
        $helper = $this->createStub(ProductHelper::class);
        $helper->method('getFieldsAutogenerationMasks')->willReturn($masks);
        $resource = $this->createStub(ProductResource::class);
        $resource->method('getIdBySku')->willReturn($productExists ? '1' : false);
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')
            ->willReturnCallback(fn (string $path) => $path === 'catalog/fields_masks_api/enabled' && $enabled);

        return new GenerateProductFieldsFromMasks($helper, $resource, $scopeConfig);
    }

    /**
     * @param array $data
     * @param int|null $id
     * @return Product
     */
    private function createProduct(array $data, ?int $id = null): Product
    {
        $product = $this->createPartialMock(Product::class, ['getId', 'getSku', 'getData', 'setCustomAttribute']);
        $product->method('getId')->willReturn($id);
        $product->method('getSku')->willReturn($data['sku'] ?? 'sku');
        $product->method('getData')->willReturnCallback(fn (string $key) => $data[$key] ?? null);

        return $product;
    }
}
