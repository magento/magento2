<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test product custom options create.
 * Testing option types: "Area", "File", "Drop-down", "Radio-Buttons",
 * "Checkbox", "Multiple Select", "Date", "Date & Time" and "Time".
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CreateCustomOptionsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Custom option factory.
     *
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->customOptionFactory = $this->objectManager->create(ProductCustomOptionInterfaceFactory::class);
        $this->customOptionValueFactory = $this->objectManager
            ->create(ProductCustomOptionValuesInterfaceFactory::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Test to save option price by store.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     *
     * @magentoConfigFixture default_store catalog/price/scope 1
     * @magentoConfigFixture secondstore_store catalog/price/scope 1
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testSaveOptionPriceByStore(): void
    {
        $secondWebsitePrice = 22.0;
        $defaultStoreId = $this->storeManager->getStore()->getId();
        $secondStoreId = $this->storeManager->getStore('secondstore')->getId();
        $product = $this->productRepository->get('simple');
        $option = $product->getOptions()[0];
        $defaultPrice = $option->getPrice();
        $option->setPrice($secondWebsitePrice);
        $product->setStoreId($secondStoreId);
        // set Current store='secondstore' to correctly save product options for 'secondstore'
        $this->storeManager->setCurrentStore($secondStoreId);
        $this->productRepository->save($product);
        $this->storeManager->setCurrentStore($defaultStoreId);
        $product = $this->productRepository->get('simple', false, Store::DEFAULT_STORE_ID, true);
        $option = $product->getOptions()[0];
        $this->assertEquals($defaultPrice, $option->getPrice(), 'Price value by default store is wrong');
        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $option = $product->getOptions()[0];
        $this->assertEquals($secondWebsitePrice, $option->getPrice(), 'Price value by store_id=1 is wrong');
    }

    /**
     * Test add to product custom options with text type.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider productCustomOptionsTypeTextDataProvider
     *
     * @param array $optionData
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCreateOptionsWithTypeText(array $optionData): void
    {
        $option = $this->baseCreateCustomOptionAndAssert($optionData);
        $this->assertEquals($optionData['price'], $option->getPrice());
        $this->assertEquals($optionData['price_type'], $option->getPriceType());
        $this->assertEquals($optionData['sku'], $option->getSku());

        if (isset($optionData['max_characters'])) {
            $this->assertEquals($optionData['max_characters'], $option->getMaxCharacters());
        } else {
            $this->assertEquals(0, $option->getMaxCharacters());
        }
    }

    /**
     * Tests removing ineligible characters from file_extension.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider fileExtensionsDataProvider
     *
     * @param string $rawExtensions
     * @param string $expectedExtensions
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function testFileExtensions(string $rawExtensions, string $expectedExtensions): void
    {
        $product = $this->productRepository->get('simple');
        $optionData = [
            'title' => 'file option',
            'type' => 'file',
            'is_require' => true,
            'sort_order' => 3,
            'price' => 30.0,
            'price_type' => 'percent',
            'sku' => 'sku3',
            'file_extension' => $rawExtensions,
            'image_size_x' => 10,
            'image_size_y' => 20,
        ];
        $fileOption = $this->customOptionFactory->create(['data' => $optionData]);
        $product->addOption($fileOption);
        $this->productRepository->save($product);
        $product = $this->productRepository->get('simple');
        $fileOption = $product->getOptions()[0];
        $actualExtensions = $fileOption->getFileExtension();
        $this->assertEquals($expectedExtensions, $actualExtensions);
    }

    /**
     * Test add to product custom options with select type.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider productCustomOptionsTypeSelectDataProvider
     *
     * @param array $optionData
     * @param array $optionValueData
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCreateOptionsWithTypeSelect(array $optionData, array $optionValueData): void
    {
        $optionValue = $this->customOptionValueFactory->create(['data' => $optionValueData]);
        $optionData['values'] = [$optionValue];
        $option = $this->baseCreateCustomOptionAndAssert($optionData);
        $optionValues = $option->getValues();
        $this->assertCount(1, $optionValues);
        $this->assertNotNull($optionValues);
        $optionValue = reset($optionValues);
        $this->assertEquals($optionValueData['title'], $optionValue->getTitle());
        $this->assertEquals($optionValueData['price'], $optionValue->getPrice());
        $this->assertEquals($optionValueData['price_type'], $optionValue->getPriceType());
        $this->assertEquals($optionValueData['sku'], $optionValue->getSku());
        $this->assertEquals($optionValueData['sort_order'], $optionValue->getSortOrder());
    }

    /**
     * Test add to product custom options with date type.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider productCustomOptionsTypeDateDataProvider
     *
     * @param array $optionData
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCreateOptionsWithTypeDate(array $optionData): void
    {
        $option = $this->baseCreateCustomOptionAndAssert($optionData);
        $this->assertEquals($optionData['price'], $option->getPrice());
        $this->assertEquals($optionData['price_type'], $option->getPriceType());
        $this->assertEquals($optionData['sku'], $option->getSku());
    }

    /**
     * Check that error throws if we save porduct with custom option without some field.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @dataProvider productCustomOptionsWithErrorDataProvider
     *
     * @param array $optionData
     * @param string $expectedErrorText
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCreateOptionWithError(array $optionData, string $expectedErrorText): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $createdOption = $this->customOptionFactory->create(['data' => $optionData]);
        $product->setOptions([$createdOption]);
        $this->expectExceptionMessage($expectedErrorText);
        $productRepository->save($product);
    }

    /**
     * Add option to product with type text data provider.
     *
     * @return array
     */
    public function productCustomOptionsTypeTextDataProvider(): array
    {
        return [
            'area_field_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'area_field_not_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'area_field_options_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'area_field_options_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'percent',
                ],
            ],
            'area_field_options_with_max_charters_configuration' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 30,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'area_field_options_without_max_charters_configuration' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
        ];
    }

    /**
     * Data provider for testFileExtensions.
     *
     * @return array
     */
    public function fileExtensionsDataProvider(): array
    {
        return [
            ['JPG, PNG, GIF', 'jpg, png, gif'],
            ['jpg, jpg, jpg', 'jpg'],
            ['jpg, png, gif', 'jpg, png, gif'],
            ['jpg png gif', 'jpg, png, gif'],
            ['!jpg@png#gif%', 'jpg, png, gif'],
            ['jpg, png, 123', 'jpg, png, 123'],
            ['', ''],
        ];
    }

    /**
     * Add option to product with type text data provider.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function productCustomOptionsTypeSelectDataProvider(): array
    {
        return [
            'drop_down_field_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'drop_down',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'drop_down_field_not_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'title' => 'Test option 1',
                    'type' => 'drop_down',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'drop_down_field_option_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'drop_down',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'drop_down_field_option_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'drop_down',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'percent',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'radio_field_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'radio',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'radio_field_not_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'title' => 'Test option 1',
                    'type' => 'radio',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'radio_field_option_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'radio',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'radio_field_option_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'radio',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'percent',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'checkbox_field_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'checkbox',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'checkbox_field_not_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'title' => 'Test option 1',
                    'type' => 'checkbox',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'checkbox_field_option_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'checkbox',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'checkbox_field_option_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'checkbox',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'percent',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'multiple_field_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'multiple',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'multiple_field_not_required_option' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'title' => 'Test option 1',
                    'type' => 'multiple',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'multiple_field_option_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'multiple',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
            'multiple_field_option_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'title' => 'Test option 1',
                    'type' => 'multiple',
                    'price_type' => 'fixed',
                ],
                [
                    'record_id' => 0,
                    'title' => 'Test option 1 value 1',
                    'price' => 10,
                    'price_type' => 'percent',
                    'sku' => 'test-option-1-value-1',
                    'sort_order' => 1,
                ],
            ],
        ];
    }

    /**
     * Add option to product with type text data provider.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function productCustomOptionsTypeDateDataProvider(): array
    {
        return [
            'date_field_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'date_field_not_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'date_field_options_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'date_field_options_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date',
                    'price' => 10,
                    'price_type' => 'percent',
                ],
            ],
            'date_time_field_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date_time',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'date_time_field_not_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date_time',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'date_time_field_options_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date_time',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'date_time_field_options_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'date_time',
                    'price' => 10,
                    'price_type' => 'percent',
                ],
            ],
            'time_field_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'time',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'time_field_not_required_options' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 0,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'time',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'time_field_options_with_fixed_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'time',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
            ],
            'time_field_options_with_percent_price' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'title' => 'Test option title 1',
                    'type' => 'time',
                    'price' => 10,
                    'price_type' => 'percent',
                ],
            ],
        ];
    }

    /**
     * Add option to product for get option save error data provider.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function productCustomOptionsWithErrorDataProvider(): array
    {
        return [
            'error_option_without_product_sku' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                ],
                'The ProductSku is empty. Set the ProductSku and try again.',
            ],
            'error_option_without_type' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'product_sku' => 'simple',
                ],
                "Missed values for option required fields\nInvalid option type",
            ],
            'error_option_wrong_price_type' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'test_wrong_price_type',
                    'product_sku' => 'simple',
                ],
                'Invalid option value',
            ],
            'error_option_without_price_type' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price' => 10,
                    'product_sku' => 'simple',
                ],
                'Invalid option value',
            ],
            'error_option_without_price_value' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => 'Test option title 1',
                    'type' => 'area',
                    'price_type' => 'fixed',
                    'product_sku' => 'simple',
                ],
                'Invalid option value',
            ],
            'error_option_without_title' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'product_sku' => 'simple',
                ],
                "Missed values for option required fields",
            ],
            'error_option_with_empty_title' => [
                [
                    'record_id' => 0,
                    'sort_order' => 1,
                    'is_require' => 1,
                    'sku' => 'test-option-title-1',
                    'max_characters' => 50,
                    'title' => '',
                    'type' => 'area',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'product_sku' => 'simple',
                ],
                "Missed values for option required fields",
            ],
        ];
    }

    /**
     * Delete all custom options from product.
     */
    protected function tearDown(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var ProductCustomOptionRepositoryInterface $optionRepository */
        $optionRepository = $this->objectManager->create(ProductCustomOptionRepositoryInterface::class);
        try {
            $product = $productRepository->get('simple');
            foreach ($optionRepository->getProductOptions($product) as $customOption) {
                $optionRepository->delete($customOption);
            }
        } catch (\Exception $e) {
        }

        parent::tearDown();
    }

    /**
     * Create custom option and save product with created option, check base assertions.
     *
     * @param array $optionData
     * @return ProductCustomOptionInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function baseCreateCustomOptionAndAssert(array $optionData): ProductCustomOptionInterface
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var ProductCustomOptionRepositoryInterface $optionRepository */
        $optionRepository = $this->objectManager->create(ProductCustomOptionRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $createdOption = $this->customOptionFactory->create(['data' => $optionData]);
        $createdOption->setProductSku($product->getSku());
        $product->setOptions([$createdOption]);
        $productRepository->save($product);
        $productCustomOptions = $optionRepository->getProductOptions($product);
        $this->assertCount(1, $productCustomOptions);
        $option = reset($productCustomOptions);
        $this->assertEquals($optionData['title'], $option->getTitle());
        $this->assertEquals($optionData['type'], $option->getType());
        $this->assertEquals($optionData['is_require'], $option->getIsRequire());

        return $option;
    }
}
