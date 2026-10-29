<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CompareTest extends TestCase
{
    /**
     * @var Compare
     */
    private $compare;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Quote
     */
    private $quote1;

    /**
     * @var Quote
     */
    private $quote2;

    /**
     * @var Quote
     */
    private $quote3;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->compare = $this->objectManager->create(Compare::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->quote1 = $this->objectManager->create(Quote::class);
        $this->quote2 = $this->objectManager->create(Quote::class);
        $this->quote3 = $this->objectManager->create(Quote::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_options.php
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function testCompare(): void
    {
        $this->quote1->setReservedOrderId('test_order_item_with_custom_options1');
        $this->quote2->setReservedOrderId('test_order_item_with_custom_options2');
        $this->quote2->setReservedOrderId('test_order_item_with_custom_options3');

        $product = $this->productRepository->get('simple_with_custom_options', true, null, true);

        $options = $this->selectOptions($product, 'test1');
        $requestInfo = new DataObject(['qty' => 1, 'options' => $options]);
        $this->quote1->addProduct($product, $requestInfo);
        $items1 = $this->quote1->getAllItems();

        $options = $this->selectOptions($product, 'test2');
        $requestInfo = new DataObject(['qty' => 1, 'options' => $options]);
        $this->quote2->addProduct($product, $requestInfo);
        $items2 = $this->quote2->getAllItems();

        $options = $this->selectOptions($product, 'test1');
        $requestInfo = new DataObject(['qty' => 1, 'options' => $options]);
        $this->quote3->addProduct($product, $requestInfo);
        $items3 = $this->quote3->getAllItems();

        $this->assertTrue($this->compare->compare($items1[0], $items1[0]));
        $this->assertTrue($this->compare->compare($items2[0], $items2[0]));
        $this->assertFalse($this->compare->compare($items1[0], $items2[0]));
        $this->assertTrue($this->compare->compare($items1[0], $items3[0]));
    }

    /**
     * Make selection of custom options
     *
     * @param ProductInterface $product
     * @param string $textValue
     * @return array
     */
    private function selectOptions(ProductInterface $product, string $textValue): array
    {
        $options = [];
        /** @var $option Option */
        foreach ($product->getOptions() as $option) {
            $value = match ($option->getGroupByType()) {
                ProductCustomOptionInterface::OPTION_GROUP_SELECT => key($option->getValues()),
                default => $textValue,
            };
            $options[$option->getId()] = $value;
        }
        return $options;
    }
}
