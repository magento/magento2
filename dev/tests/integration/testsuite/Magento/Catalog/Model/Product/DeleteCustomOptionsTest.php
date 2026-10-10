<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Area;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Checkbox;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Date;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\DateTime;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\DropDown;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\File;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\MultipleSelect;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\RadioButtons;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Time;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

/**
 * Test delete product custom options.
 * Testing option types: "Area", "File", "Drop-down", "Radio-Buttons",
 * "Checkbox", "Multiple Select", "Date", "Date & Time" and "Time".
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteCustomOptionsTest extends TestCase
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->optionRepository = $this->objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $this->customOptionFactory = $this->objectManager->get(ProductCustomOptionInterfaceFactory::class);
        $this->customOptionValueFactory = $this->objectManager
            ->get(ProductCustomOptionValuesInterfaceFactory::class);

        parent::setUp();
    }

    /**
     * Test delete product custom options with type "area".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @return void
     */
    #[DataProviderExternal(Area::class, 'getDataForCreateOptions')]
    public function testDeleteAreaCustomOption(array $optionData): void
    {
        $this->deleteAndAssertNotSelectCustomOptions($optionData);
    }

    /**
     * Test delete product custom options with type "file".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @return void
     */
    #[DataProviderExternal(File::class, 'getDataForCreateOptions')]
    public function testDeleteFileCustomOption(array $optionData): void
    {
        $this->deleteAndAssertNotSelectCustomOptions($optionData);
    }

    /**
     * Test delete product custom options with type "Date".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @return void
     */
    #[DataProviderExternal(Date::class, 'getDataForCreateOptions')]
    public function testDeleteDateCustomOption(array $optionData): void
    {
        $this->deleteAndAssertNotSelectCustomOptions($optionData);
    }

    /**
     * Test delete product custom options with type "Date & Time".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @return void
     */
    #[DataProviderExternal(DateTime::class, 'getDataForCreateOptions')]
    public function testDeleteDateTimeCustomOption(array $optionData): void
    {
        $this->deleteAndAssertNotSelectCustomOptions($optionData);
    }

    /**
     * Test delete product custom options with type "Time".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @return void
     */
    #[DataProviderExternal(Time::class, 'getDataForCreateOptions')]
    public function testDeleteTimeCustomOption(array $optionData): void
    {
        $this->deleteAndAssertNotSelectCustomOptions($optionData);
    }

    /**
     * Test delete product custom options with type "Drop-down".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @param array $optionValueData
     * @return void
     */
    #[DataProviderExternal(DropDown::class, 'getDataForCreateOptions')]
    public function testDeleteDropDownCustomOption(array $optionData, array $optionValueData): void
    {
        $this->deleteAndAssertSelectCustomOptions($optionData, $optionValueData);
    }

    /**
     * Test delete product custom options with type "Radio Buttons".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @param array $optionValueData
     * @return void
     */
    #[DataProviderExternal(RadioButtons::class, 'getDataForCreateOptions')]
    public function testDeleteRadioButtonsCustomOption(array $optionData, array $optionValueData): void
    {
        $this->deleteAndAssertSelectCustomOptions($optionData, $optionValueData);
    }

    /**
     * Test delete product custom options with type "Checkbox".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @param array $optionValueData
     * @return void
     */
    #[DataProviderExternal(Checkbox::class, 'getDataForCreateOptions')]
    public function testDeleteCheckboxCustomOption(array $optionData, array $optionValueData): void
    {
        $this->deleteAndAssertSelectCustomOptions($optionData, $optionValueData);
    }

    /**
     * Test delete product custom options with type "Multiple Select".
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @param array $optionData
     * @param array $optionValueData
     * @return void
     */
    #[DataProviderExternal(MultipleSelect::class, 'getDataForCreateOptions')]
    public function testDeleteMultipleSelectCustomOption(array $optionData, array $optionValueData): void
    {
        $this->deleteAndAssertSelectCustomOptions($optionData, $optionValueData);
    }

    /**
     * Delete product custom options which are not from "select" group and assert that option was deleted.
     *
     * @param array $optionData
     * @return void
     */
    private function deleteAndAssertNotSelectCustomOptions(array $optionData): void
    {
        $product = $this->productRepository->get('simple');
        $createdOption = $this->customOptionFactory->create(['data' => $optionData]);
        $createdOption->setProductSku($product->getSku());
        $product->setOptions([$createdOption]);
        $this->productRepository->save($product);
        $this->assertCount(1, $this->optionRepository->getProductOptions($product));
        $product->setOptions([]);
        $this->productRepository->save($product);
        $this->assertCount(0, $this->optionRepository->getProductOptions($product));
    }

    /**
     * Delete product custom options which from "select" group and assert that option was deleted.
     *
     * @param array $optionData
     * @param array $optionValueData
     * @return void
     */
    private function deleteAndAssertSelectCustomOptions(array $optionData, array $optionValueData): void
    {
        $optionValue = $this->customOptionValueFactory->create(['data' => $optionValueData]);
        $optionData['values'] = [$optionValue];
        $this->deleteAndAssertNotSelectCustomOptions($optionData);
    }
}
