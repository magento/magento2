<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View\Attributes;
use Magento\Catalog\Helper\Output;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class consist of base logic for custom attributes view tests
 */
abstract class AbstractAttributeTest extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductAttributeRepositoryInterface */
    private $attributeRepository;

    /** @var Registry */
    private $registry;

    /** @var  Attributes */
    private $block;

    /** @var ProductAttributeInterface */
    private $attribute;

    /** @var Output */
    private $outputHelper;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->block = $this->layout->createBlock(Attributes::class);
        $this->outputHelper = $this->objectManager->create(Output::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Process custom attribute view
     *
     * @param string $sku
     * @param string $attributeValue
     * @param string $expectedAttributeValue
     * @return void
     */
    protected function processAttributeView(
        string $sku,
        string $attributeValue,
        string $expectedAttributeValue
    ): void {
        $this->updateAttribute(['is_visible_on_front' => true]);
        $product = $this->updateProduct($sku, $attributeValue);
        $this->registerProduct($product);
        $data = $this->block->getAdditionalData();
        $this->assertEquals($this->prepareExpectedData($expectedAttributeValue), $data);
    }

    /**
     * Process custom attribute with default value view when new value set
     *
     * @param string $sku
     * @param string $attributeValue
     * @param string $expectedAttributeValue
     * @return void
     */
    protected function processNonDefaultAttributeValueView(
        string $sku,
        string $attributeValue,
        string $expectedAttributeValue
    ): void {
        $this->updateAttribute(['is_visible_on_front' => true, 'default_value' => $this->getDefaultAttributeValue()]);
        $product = $this->updateProduct($sku, $attributeValue);
        $this->registerProduct($product);
        $data = $this->block->getAdditionalData();
        $this->assertEquals($this->prepareExpectedData($expectedAttributeValue), $data);
    }

    /**
     * Procces custom attribute view with default value
     *
     * @param string $sku
     * @param string $expectedAttributeValue
     * @return void
     */
    protected function processDefaultValueAttributeView(string $sku, string $expectedAttributeValue): void
    {
        $this->updateAttribute(['is_visible_on_front' => true, 'default_value' => $this->getDefaultAttributeValue()]);
        $product = $this->productRepository->save($this->productRepository->get($sku));
        $this->registerProduct($product);
        $data = $this->block->getAdditionalData();
        $this->assertEquals($this->prepareExpectedData($expectedAttributeValue), $data);
    }

    /**
     * Procces attribute value view with html tags
     *
     * @param string $sku
     * @param bool $allowHtmlTags
     * @param string $attributeValue
     * @param string $expectedAttributeValue
     * @return void
     */
    protected function processAttributeHtmlOutput(
        string $sku,
        bool $allowHtmlTags,
        string $attributeValue,
        string $expectedAttributeValue
    ): void {
        $this->updateAttribute(['is_visible_on_front' => true, 'is_html_allowed_on_front' => $allowHtmlTags]);
        $product = $this->updateProduct($sku, $attributeValue);
        $this->registerProduct($product);
        $data = $this->block->getAdditionalData();
        $dataItem = $data[$this->getAttributeCode()] ?? null;
        $this->assertNotNull($dataItem);
        $output = $this->outputHelper->productAttribute($product, $dataItem['value'], $dataItem['code']);
        $this->assertEquals($expectedAttributeValue, $output);
    }

    /**
     * Process attribute view per store views
     *
     * @param string $sku
     * @param int $attributeScopeValue
     * @param string $attributeValue
     * @param string $expectedAttributeValue
     * @param string $storeCode
     * @return void
     */
    protected function processMultiStoreView(
        string $sku,
        int $attributeScopeValue,
        string $attributeValue,
        string $storeCode
    ): void {
        $currentStore = $this->storeManager->getStore();
        $this->updateAttribute(['is_global' => $attributeScopeValue, 'is_visible_on_front' => true]);
        $this->storeManager->setCurrentStore($storeCode);

        try {
            $product = $this->updateProduct($sku, $attributeValue);
            $this->registerProduct($product);
            $this->assertEquals($this->prepareExpectedData($attributeValue), $this->block->getAdditionalData());
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * Get attribute
     *
     * @return ProductAttributeInterface
     */
    protected function getAttribute(): ProductAttributeInterface
    {
        if ($this->attribute === null) {
            $this->attribute = $this->attributeRepository->get($this->getAttributeCode());
        }

        return $this->attribute;
    }

    /**
     * Prepare expected data
     *
     * @param string $expectedValue
     * @return array
     */
    private function prepareExpectedData(string $expectedValue): array
    {
        return [
            $this->getAttributeCode() => [
                'label' => $this->getAttribute()->getStoreLabel(),
                'value' => $expectedValue,
                'code' => $this->getAttributeCode(),
            ],
        ];
    }

    /**
     * Update product
     *
     * @param string $productSku
     * @param string $attributeValue
     * @return ProductInterface
     */
    private function updateProduct(string $productSku, string $attributeValue): ProductInterface
    {
        $value = $this->getAttribute()->usesSource()
            ? $this->attribute->getSource()->getOptionId($attributeValue)
            : $attributeValue;
        $product = $this->productRepository->get($productSku);
        $product->addData([$this->getAttributeCode() => $value]);

        return $this->productRepository->save($product);
    }

    /**
     * Register product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }

    /**
     * Update attribute
     *
     * @param array $data
     * @return void
     */
    private function updateAttribute(array $data): void
    {
        $attribute = $this->getAttribute();
        $attribute->addData($data);

        $this->attributeRepository->save($attribute);
    }

    /**
     * Get attribute code for current test
     *
     * @return string
     */
    abstract protected function getAttributeCode(): string;

    /**
     * Get default value for current attribute
     *
     * @return string
     */
    abstract protected function getDefaultAttributeValue(): string;
}
