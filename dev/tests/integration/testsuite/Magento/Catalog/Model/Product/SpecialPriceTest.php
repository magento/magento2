<?php
/**
 * Copyright 2025 Adobe
 * * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for special price functionality
 */
class SpecialPriceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private mixed $productRepository;

    /**
     * @var TimezoneInterface
     */
    private mixed $localeDate;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->localeDate = $this->objectManager->get(TimezoneInterface::class);
    }

    /**
     * Test that special_from_date is automatically set when special price is set via API
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSpecialFromDateSetWhenSpecialPriceSet(): void
    {
        $product = $this->productRepository->get('simple');
        $product->setSpecialPrice(5.99);
        $product->setSpecialFromDate(null);

        $this->productRepository->save($product);
        $updatedProduct = $this->productRepository->get('simple', false, null, true);
        $this->assertNotNull($updatedProduct->getSpecialFromDate());
        $expectedDate = $this->localeDate->date()->setTime(0, 0, 0)->format('Y-m-d');
        $actualDate = substr($updatedProduct->getSpecialFromDate(), 0, 10);

        // Assert special_from_date is set to current date with time 00:00:00
        $this->assertEquals($expectedDate, $actualDate);
    }

    /**
     * Test that existing special_from_date is not changed when product is saved
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExistingSpecialFromDateNotChanged(): void
    {
        $product = $this->productRepository->get('simple');

        $specificDate = '2023-01-01 00:00:00';
        $product->setSpecialPrice(5.99);
        $product->setSpecialFromDate($specificDate);

        $this->productRepository->save($product);
        $updatedProduct = $this->productRepository->get('simple', false, null, true);

        // Assert special_from_date remains unchanged
        $this->assertEquals($specificDate, $updatedProduct->getSpecialFromDate());
    }

    /**
     * Test that special price is correctly applied when special_from_date is today
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSpecialPriceAppliedWithTodayDate(): void
    {
        $product = $this->productRepository->get('simple');
        $regularPrice = 10.00;
        $specialPrice = 5.99;

        $product->setPrice($regularPrice);
        $today = $this->localeDate->date()->format('Y-m-d 00:00:00');
        $product->setSpecialPrice($specialPrice);
        $product->setSpecialFromDate($today);

        $this->productRepository->save($product);
        $updatedProduct = $this->productRepository->get('simple', false, null, true);

        // Assert special price is applied
        $this->assertEquals($specialPrice, $updatedProduct->getFinalPrice());
    }
}
