<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test product custom options update.
 * Testing option types: "Area", "File", "Drop-down", "Radio-Buttons",
 * "Checkbox", "Multiple Select", "Date", "Date & Time" and "Time".
 *
 * @magentoDbIsolation enabled
 */
class UpdateCustomOptionsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $customOptionFactory;

    /**
     * @var ProductCustomOptionValuesInterfaceFactory
     */
    private $customOptionValueFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreInterface
     */
    private $currentStoreId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->optionRepository = $this->objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $this->customOptionFactory = $this->objectManager->get(ProductCustomOptionInterfaceFactory::class);
        $this->customOptionValueFactory = $this->objectManager
            ->get(ProductCustomOptionValuesInterfaceFactory::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->currentStoreId = $this->storeManager->getStore()->getId();
        $adminStoreId = $this->storeManager->getStore('admin')->getId();
        $this->storeManager->setCurrentStore($adminStoreId);

        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->storeManager->setCurrentStore($this->currentStoreId);

        parent::tearDown();
    }

    /**
     * Test update product custom options with type "area".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Area::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    public function testUpdateAreaCustomOption(array $optionData, array $updateData): void
    {
        $this->updateAndAssertNotSelectCustomOptions($optionData, $updateData);
    }

    /**
     * Test update product custom options with type "file".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\File::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    public function testUpdateFileCustomOption(array $optionData, array $updateData): void
    {
        $this->updateAndAssertNotSelectCustomOptions($optionData, $updateData);
    }

    /**
     * Test update product custom options with type "Date".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Date::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    public function testUpdateDateCustomOption(array $optionData, array $updateData): void
    {
        $this->updateAndAssertNotSelectCustomOptions($optionData, $updateData);
    }

    /**
     * Test update product custom options with type "Date & Time".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\DateTime::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    public function testUpdateDateTimeCustomOption(array $optionData, array $updateData): void
    {
        $this->updateAndAssertNotSelectCustomOptions($optionData, $updateData);
    }

    /**
     * Test update product custom options with type "Time".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Time::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    public function testUpdateTimeCustomOption(array $optionData, array $updateData): void
    {
        $this->updateAndAssertNotSelectCustomOptions($optionData, $updateData);
    }

    /**
     * Test update product custom options with type "Drop-down".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\DropDown::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $updateOptionData
     * @param array $updateOptionValueData
     * @return void
     */
    public function testUpdateDropDownCustomOption(
        array $optionData,
        array $optionValueData,
        array $updateOptionData,
        array $updateOptionValueData
    ): void {
        $this->updateAndAssertSelectCustomOptions(
            $optionData,
            $optionValueData,
            $updateOptionData,
            $updateOptionValueData
        );
    }

    /**
     * Test update product custom options with type "Radio Buttons".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\RadioButtons::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $updateOptionData
     * @param array $updateOptionValueData
     * @return void
     */
    public function testUpdateRadioButtonsCustomOption(
        array $optionData,
        array $optionValueData,
        array $updateOptionData,
        array $updateOptionValueData
    ): void {
        $this->updateAndAssertSelectCustomOptions(
            $optionData,
            $optionValueData,
            $updateOptionData,
            $updateOptionValueData
        );
    }

    /**
     * Test update product custom options with type "Checkbox".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Checkbox::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $updateOptionData
     * @param array $updateOptionValueData
     * @return void
     */
    public function testUpdateCheckboxCustomOption(
        array $optionData,
        array $optionValueData,
        array $updateOptionData,
        array $updateOptionValueData
    ): void {
        $this->updateAndAssertSelectCustomOptions(
            $optionData,
            $optionValueData,
            $updateOptionData,
            $updateOptionValueData
        );
    }

    /**
     * Test update product custom options with type "Multiple Select".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\MultipleSelect::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $updateOptionData
     * @param array $updateOptionValueData
     * @return void
     */
    public function testUpdateMultipleSelectCustomOption(
        array $optionData,
        array $optionValueData,
        array $updateOptionData,
        array $updateOptionValueData
    ): void {
        $this->updateAndAssertSelectCustomOptions(
            $optionData,
            $optionValueData,
            $updateOptionData,
            $updateOptionValueData
        );
    }

    /**
     * Update product custom options which are not from "select" group and assert updated data.
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    private function updateAndAssertNotSelectCustomOptions(array $optionData, array $updateData): void
    {
        $productSku = 'simple';
        $createdOption = $this->createCustomOption($optionData, $productSku);
        $updatedOption = $this->updateOptionWithValues($updateData, $productSku);

        foreach ($updateData as $methodKey => $newValue) {
            $this->assertEquals($newValue, $updatedOption->getDataUsingMethod($methodKey));
            $this->assertNotEquals(
                $createdOption->getDataUsingMethod($methodKey),
                $updatedOption->getDataUsingMethod($methodKey)
            );
        }

        $this->assertEquals($createdOption->getOptionId(), $updatedOption->getOptionId());
    }

    /**
     * Update product custom options which from "select" group and assert updated data.
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param array $updateOptionData
     * @param array $updateOptionValueData
     * @return void
     */
    private function updateAndAssertSelectCustomOptions(
        array $optionData,
        array $optionValueData,
        array $updateOptionData,
        array $updateOptionValueData
    ): void {
        $productSku = 'simple';
        $createdOption = $this->createCustomOptionWithValue($optionData, $optionValueData, $productSku);
        $createdOptionValue = $this->getOptionValue($createdOption);
        $updatedOption = $this->updateOptionAndValueWithValues($updateOptionData, $updateOptionValueData, $productSku);
        $updatedOptionValue = $this->getOptionValue($updatedOption);

        foreach ($updateOptionData as $methodKey => $newValue) {
            $this->assertEquals($newValue, $updatedOption->getDataUsingMethod($methodKey));
            $this->assertNotEquals(
                $createdOption->getDataUsingMethod($methodKey),
                $updatedOption->getDataUsingMethod($methodKey)
            );
        }

        foreach ($updateOptionValueData as $methodKey => $newValue) {
            $methodName = str_replace('_', '', ucwords($methodKey, '_'));
            $this->assertEquals($newValue, $updatedOptionValue->{'get' . $methodName}());
            $this->assertNotEquals(
                $createdOptionValue->getDataUsingMethod($methodKey),
                $updatedOptionValue->getDataUsingMethod($methodKey)
            );
        }

        $this->assertEquals($createdOption->getOptionId(), $updatedOption->getOptionId());
    }

    /**
     * Create custom option and save product with created option.
     *
     * @param array $optionData
     * @param string $productSku
     * @return ProductCustomOptionInterface|Option
     */
    private function createCustomOption(array $optionData, string $productSku): ProductCustomOptionInterface
    {
        $product = $this->productRepository->get($productSku);
        $createdOption = $this->customOptionFactory->create(['data' => $optionData]);
        $createdOption->setProductSku($product->getSku());
        $product->setOptions([$createdOption]);
        $this->productRepository->save($product);
        $productCustomOptions = $this->optionRepository->getProductOptions($product);
        $option = reset($productCustomOptions);

        return $option;
    }

    /**
     * Create custom option from select group and save product with created option.
     *
     * @param array $optionData
     * @param array $optionValueData
     * @param string $productSku
     * @return ProductCustomOptionInterface|Option
     */
    private function createCustomOptionWithValue(
        array $optionData,
        array $optionValueData,
        string $productSku
    ): ProductCustomOptionInterface {
        $optionValue = $this->customOptionValueFactory->create(['data' => $optionValueData]);
        $optionData['values'] = [$optionValue];

        return $this->createCustomOption($optionData, $productSku);
    }

    /**
     * Update product option with values.
     *
     * @param array $updateData
     * @param string $productSku
     * @return ProductCustomOptionInterface|Option
     */
    private function updateOptionWithValues(array $updateData, string $productSku): ProductCustomOptionInterface
    {
        $product = $this->productRepository->get($productSku);
        $currentOption = $this->getProductOptionByProductSku($product->getSku());
        $currentOption->setProductSku($product->getSku());
        foreach ($updateData as $methodKey => $newValue) {
            $currentOption->setDataUsingMethod($methodKey, $newValue);
        }
        $product->setOptions([$currentOption]);
        $this->productRepository->save($product);

        return $this->getProductOptionByProductSku($product->getSku());
    }

    /**
     * Update product option with values.
     *
     * @param array $optionUpdateData
     * @param array $optionValueUpdateData
     * @param string $productSku
     * @return ProductCustomOptionInterface|Option
     */
    private function updateOptionAndValueWithValues(
        array $optionUpdateData,
        array $optionValueUpdateData,
        string $productSku
    ): ProductCustomOptionInterface {
        $product = $this->productRepository->get($productSku);
        $currentOption = $this->getProductOptionByProductSku($product->getSku());
        $currentOption->setProductSku($product->getSku());
        $optionValue = $this->getOptionValue($currentOption);
        foreach ($optionUpdateData as $methodKey => $newValue) {
            $currentOption->setDataUsingMethod($methodKey, $newValue);
        }
        foreach ($optionValueUpdateData as $methodKey => $newValue) {
            $optionValue->setDataUsingMethod($methodKey, $newValue);
        }
        $currentOption->setValues([$optionValue]);
        $product->setOptions([$currentOption]);
        $this->productRepository->save($product);

        return $this->getProductOptionByProductSku($product->getSku());
    }

    /**
     * Get product option by product sku.
     *
     * @param string $productSku
     * @return ProductCustomOptionInterface|Option
     */
    private function getProductOptionByProductSku(string $productSku): ProductCustomOptionInterface
    {
        $product = $this->productRepository->get($productSku);
        $currentOptions = $this->optionRepository->getProductOptions($product);

        return reset($currentOptions);
    }

    /**
     * Return custom option value.
     *
     * @param ProductCustomOptionInterface $customOption
     * @return ProductCustomOptionValuesInterface|Value
     */
    private function getOptionValue(ProductCustomOptionInterface $customOption): ProductCustomOptionValuesInterface
    {
        $optionValues = $customOption->getValues();

        return reset($optionValues);
    }
}
