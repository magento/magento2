<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Options;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View\Options;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Base logic for render custom options and check that option renders as expected.
 */
abstract class AbstractRenderCustomOptionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $productCustomOptionFactory;

    /**
     * @var ProductCustomOptionValuesInterfaceFactory
     */
    private $productCustomOptionValuesFactory;

    /**
     * @var Page
     */
    private $page;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->productCustomOptionFactory = $this->objectManager->get(ProductCustomOptionInterfaceFactory::class);
        $this->productCustomOptionValuesFactory = $this->objectManager->get(
            ProductCustomOptionValuesInterfaceFactory::class
        );
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->productRepository->cleanCache();
        parent::tearDown();
    }

    /**
     * Add provided options from text group to product, render options block
     * and check that options rendered as expected.
     *
     * @param string $productSku
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    protected function assertTextOptionRenderingOnProduct(
        string $productSku,
        array $optionData,
        array $checkArray
    ): void {
        $product = $this->productRepository->get($productSku);
        $product = $this->addOptionToProduct($product, $optionData);
        $option = $this->findOptionByTitle($product, $optionData[Option::KEY_TITLE]);
        $optionHtml = $this->getOptionHtml($product);
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);
        $this->additionalTypeTextAsserts($option, $optionHtml, $checkArray);
    }

    /**
     * Additional asserts for rendering text type options.
     *
     * @param ProductCustomOptionInterface $option
     * @param string $optionHtml
     * @param array $checkArray
     * @return void
     */
    protected function additionalTypeTextAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        if ($option->getMaxCharacters() > 0) {
            $this->assertStringContainsString($checkArray['max_characters'], $optionHtml);
        } else {
            $this->assertStringNotContainsString($this->getMaxCharactersCssClass(), $optionHtml);
        }
    }

    /**
     * Add provided options from file group to product, render options block
     * and check that options rendered as expected.
     *
     * @param string $productSku
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    protected function assertFileOptionRenderingOnProduct(
        string $productSku,
        array $optionData,
        array $checkArray
    ): void {
        $product = $this->productRepository->get($productSku);
        $product = $this->addOptionToProduct($product, $optionData);
        $option = $this->findOptionByTitle($product, $optionData[Option::KEY_TITLE]);
        $optionHtml = $this->getOptionHtml($product);
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);
        $this->assertStringContainsString($checkArray['file_extension'], $optionHtml);

        if (isset($checkArray['file_width'])) {
            $checkArray['file_width'] = sprintf($checkArray['file_width'], __('Maximum image width'));
            $this->assertMatchesRegularExpression($checkArray['file_width'], $optionHtml);
        }

        if (isset($checkArray['file_height'])) {
            $checkArray['file_height'] = sprintf($checkArray['file_height'], __('Maximum image height'));
            $this->assertMatchesRegularExpression($checkArray['file_height'], $optionHtml);
        }
    }

    /**
     * Add provided options from select group to product, render options block
     * and check that options rendered as expected.
     *
     * @param string $productSku
     * @param array $optionData
     * @param array $optionValueData
     * @param array $checkArray
     * @return void
     */
    protected function assertSelectOptionRenderingOnProduct(
        string $productSku,
        array $optionData,
        array $optionValueData,
        array $checkArray
    ): void {
        $product = $this->productRepository->get($productSku);
        $product = $this->addOptionToProduct($product, $optionData, $optionValueData);
        $option = $this->findOptionByTitle($product, $optionData[Option::KEY_TITLE]);
        $optionHtml = $this->getOptionHtml($product);
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);
        $this->additionalTypeSelectAsserts($option, $optionHtml, $checkArray);
    }

    /**
     * Additional asserts for rendering select type options.
     *
     * @param ProductCustomOptionInterface $option
     * @param string $optionHtml
     * @param array $checkArray
     * @return void
     */
    protected function additionalTypeSelectAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        $optionValues = $option->getValues();
        $optionValue = reset($optionValues);
        if (isset($checkArray['not_contain_arr'])) {
            foreach ($checkArray['not_contain_arr'] as $notContainPattern) {
                $this->assertDoesNotMatchRegularExpression($notContainPattern, $optionHtml);
            }
        }
        if (isset($checkArray['option_value_item'])) {
            $checkArray['option_value_item'] = sprintf(
                $checkArray['option_value_item'],
                $optionValue->getOptionTypeId(),
                $optionValue->getTitle()
            );
            $this->assertMatchesRegularExpression($checkArray['option_value_item'], $optionHtml);
        }
    }

    /**
     * Add provided options from date group to product, render options block
     * and check that options rendered as expected.
     *
     * @param string $productSku
     * @param array $optionData
     * @param array $checkArray
     * @return void
     */
    protected function assertDateOptionRenderingOnProduct(
        string $productSku,
        array $optionData,
        array $checkArray
    ): void {
        $product = $this->productRepository->get($productSku);
        $product = $this->addOptionToProduct($product, $optionData);
        $option = $this->findOptionByTitle($product, $optionData[Option::KEY_TITLE]);
        $optionHtml = $this->getOptionHtml($product);
        $this->baseOptionAsserts($option, $optionHtml, $checkArray);

        switch ($optionData[Option::KEY_TYPE]) {
            case ProductCustomOptionInterface::OPTION_TYPE_DATE:
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][month]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][day]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][year]\"",
                    $optionHtml
                );
                $this->assertStringNotContainsString(
                    "<select name=\"options[{$option->getOptionId()}][hour]\"",
                    $optionHtml
                );
                $this->assertStringNotContainsString(
                    "<select name=\"options[{$option->getOptionId()}][minute]\"",
                    $optionHtml
                );
                $this->assertStringNotContainsString(
                    "<select name=\"options[{$option->getOptionId()}][day_part]\"",
                    $optionHtml
                );
                break;
            case ProductCustomOptionInterface::OPTION_TYPE_DATE_TIME:
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][month]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][day]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][year]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][hour]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][minute]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][day_part]\"",
                    $optionHtml
                );
                break;
            case ProductCustomOptionInterface::OPTION_TYPE_TIME:
                $this->assertStringNotContainsString(
                    "<select name=\"options[{$option->getOptionId()}][month]\"",
                    $optionHtml
                );
                $this->assertStringNotContainsString(
                    "<select name=\"options[{$option->getOptionId()}][day]\"",
                    $optionHtml
                );
                $this->assertStringNotContainsString(
                    "<select name=\"options[{$option->getOptionId()}][year]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][hour]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][minute]\"",
                    $optionHtml
                );
                $this->assertStringContainsString(
                    "<select name=\"options[{$option->getOptionId()}][day_part]\"",
                    $optionHtml
                );
                break;
        }
    }

    /**
     * Base asserts for rendered options.
     *
     * @param ProductCustomOptionInterface $option
     * @param string $optionHtml
     * @param array $checkArray
     * @return void
     */
    protected function baseOptionAsserts(
        ProductCustomOptionInterface $option,
        string $optionHtml,
        array $checkArray
    ): void {
        $this->assertStringContainsString($checkArray['block_with_required_class'], $optionHtml);
        $this->assertStringContainsString($checkArray['title'], $optionHtml);

        if (isset($checkArray['label_for_created_option'])) {
            $checkArray['label_for_created_option'] = sprintf(
                $checkArray['label_for_created_option'],
                $option->getOptionId()
            );
            $this->assertStringContainsString($checkArray['label_for_created_option'], $optionHtml);
        }

        if (isset($checkArray['price'])) {
            $this->assertStringContainsString($checkArray['price'], $optionHtml);
        }

        if (isset($checkArray['required_element'])) {
            $this->assertMatchesRegularExpression($checkArray['required_element'], $optionHtml);
        }
    }

    /**
     * Add custom option to product with data.
     *
     * @param ProductInterface $product
     * @param array $optionData
     * @param array $optionValueData
     * @return ProductInterface
     */
    protected function addOptionToProduct(
        ProductInterface $product,
        array $optionData,
        array $optionValueData = []
    ): ProductInterface {
        $optionData[Option::KEY_PRODUCT_SKU] = $product->getSku();

        if (!empty($optionValueData)) {
            $optionValueData = $this->productCustomOptionValuesFactory->create(['data' => $optionValueData]);
            $optionData['values'] = [$optionValueData];
        }

        $option = $this->productCustomOptionFactory->create(['data' => $optionData]);
        $product->setOptions([$option]);

        return $this->productRepository->save($product);
    }

    /**
     * Render custom options block.
     *
     * @param ProductInterface $product
     * @return string
     */
    protected function getOptionHtml(ProductInterface $product): string
    {
        $this->page->addHandle($this->getHandlesList());
        $this->page->getLayout()->generateXml();
        /** @var Options $optionsBlock */
        $optionsBlock = $this->page->getLayout()->getBlock($this->getOptionsBlockName());
        $this->assertNotFalse($optionsBlock);
        $optionsBlock->setProduct($product);

        return $optionsBlock->toHtml();
    }

    /**
     * Find and return custom option.
     *
     * @param ProductInterface $product
     * @param string $optionTitle
     * @return null|Option
     */
    protected function findOptionByTitle(ProductInterface $product, string $optionTitle): ?Option
    {
        $option = null;
        foreach ($product->getOptions() as $customOption) {
            if ($customOption->getTitle() === $optionTitle) {
                $option = $customOption;
                break;
            }
        }

        return $option;
    }

    /**
     * Find and return custom option value.
     *
     * @param ProductCustomOptionInterface $option
     * @param string $optionValueTitle
     * @return null|ProductCustomOptionValuesInterface
     */
    protected function findOptionValueByTitle(
        ProductCustomOptionInterface $option,
        string $optionValueTitle
    ): ?ProductCustomOptionValuesInterface {
        $optionValue = null;
        foreach ($option->getValues() as $customOptionValue) {
            if ($customOptionValue->getTitle() === $optionValueTitle) {
                $optionValue = $customOptionValue;
                break;
            }
        }

        return $optionValue;
    }

    /**
     * Return all need handles for load.
     *
     * @return array
     */
    abstract protected function getHandlesList(): array;

    /**
     * @return string
     */
    abstract protected function getMaxCharactersCssClass(): string;

    /**
     * @return string
     */
    abstract protected function getOptionsBlockName(): string;
}
