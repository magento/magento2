<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRender;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Url collector
 */
class UrlTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Url
     */
    private $urlCollector;

    /**
     * @var ButtonInterfaceFactory
     */
    private $buttonFactory;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->urlCollector = $this->objectManager->get(Url::class);
        $this->buttonFactory = $this->objectManager->get(ButtonInterfaceFactory::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test URL collector with simple product
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$']),
    ]
    public function testCollectWithNullCompareButton()
    {
        $product = $this->fixtures->get('product');
        $productRender = $this->createProductRenderWithNullButtons();
        $this->urlCollector->collect($product, $productRender);

        // Verify buttons were created and configured
        $addToCartButton = $productRender->getAddToCartButton();
        $addToCompareButton = $productRender->getAddToCompareButton();

        $this->assertInstanceOf(ButtonInterface::class, $addToCartButton);
        $this->assertInstanceOf(ButtonInterface::class, $addToCompareButton);

        // Verify add-to-cart button configuration
        $this->assertNotEmpty($addToCartButton->getPostData());
        $this->assertNotEmpty($addToCartButton->getUrl());
        $this->assertIsString($addToCartButton->getUrl());

        // Verify add-to-compare button configuration
        $compareUrl = $addToCompareButton->getUrl();
        $this->assertNotEmpty($compareUrl);

        // Verify product URL is set
        $this->assertNotEmpty($productRender->getUrl());
        $this->assertIsString($productRender->getUrl());
    }

    /**
     * Test URL collector when ProductRender already has compare button
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$']),
    ]
    public function testCollectWithExistingAddToCompareButton()
    {
        $product = $this->fixtures->get('product');
        $productRender = $this->createProductRenderWithExistingCompareButton();
        $originalCompareButton = $productRender->getAddToCompareButton();
        $this->urlCollector->collect($product, $productRender);

        // Verify the same compare button instance is used (line 82 behavior)
        $this->assertSame($originalCompareButton, $productRender->getAddToCompareButton());

        // Verify new cart button was created (since it was null)
        $addToCartButton = $productRender->getAddToCartButton();
        $this->assertInstanceOf(ButtonInterface::class, $addToCartButton);
        $this->assertNotEmpty($addToCartButton->getUrl());

        // Verify compare button was properly configured
        $this->assertNotEmpty($originalCompareButton->getUrl());
    }

    /**
     * Test URL collector integration with real Compare helper
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$']),
    ]
    public function testCollectIntegrationWithCompareHelper()
    {
        $product = $this->fixtures->get('product');
        $productRender = $this->createProductRenderWithNullButtons();
        $this->urlCollector->collect($product, $productRender);

        // Verify compare URL contains expected parameters
        $addToCompareButton = $productRender->getAddToCompareButton();
        $compareUrl = $addToCompareButton->getUrl();

        // The compare helper returns JSON formatted data
        $this->assertIsString($compareUrl);
        $this->assertNotEmpty($compareUrl);

        // Decode and verify structure
        $compareData = json_decode($compareUrl, true);
        $this->assertIsArray($compareData);
        $this->assertArrayHasKey('action', $compareData);
        $this->assertArrayHasKey('data', $compareData);
        $this->assertStringContainsString('catalog/product_compare/add', $compareData['action']);
    }

    /**
     * Test URL collector with both buttons existing
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$']),
    ]
    public function testCollectWithBothButtonsExisting()
    {
        $product = $this->fixtures->get('product');
        $productRender = $this->createProductRenderWithBothButtons();
        $originalCartButton = $productRender->getAddToCartButton();
        $originalCompareButton = $productRender->getAddToCompareButton();
        $this->urlCollector->collect($product, $productRender);

        // Verify the same button instances are used (no new buttons created)
        $this->assertSame($originalCartButton, $productRender->getAddToCartButton());
        $this->assertSame($originalCompareButton, $productRender->getAddToCompareButton());

        // Verify buttons were properly configured
        $this->assertNotEmpty($originalCartButton->getUrl());
        $this->assertNotEmpty($originalCompareButton->getUrl());
    }

    /**
     * Create ProductRender object with null buttons
     *
     * @return ProductRenderInterface
     */
    private function createProductRenderWithNullButtons(): ProductRenderInterface
    {
        return $this->objectManager->create(ProductRender::class);
    }

    /**
     * Create ProductRender object with existing compare button
     *
     * @return ProductRenderInterface
     */
    private function createProductRenderWithExistingCompareButton(): ProductRenderInterface
    {
        $productRender = $this->objectManager->create(ProductRender::class);

        // Create an existing compare button
        $existingCompareButton = $this->buttonFactory->create();

        // Only set compare button, leave cart button as null (not set)
        $productRender->setAddToCompareButton($existingCompareButton);

        return $productRender;
    }

    /**
     * Create ProductRender object with both buttons existing
     *
     * @return ProductRenderInterface
     */
    private function createProductRenderWithBothButtons(): ProductRenderInterface
    {
        $productRender = $this->objectManager->create(ProductRender::class);

        // Create both buttons to test existing button scenario
        $existingCartButton = $this->buttonFactory->create();
        $existingCompareButton = $this->buttonFactory->create();

        $productRender->setAddToCartButton($existingCartButton);
        $productRender->setAddToCompareButton($existingCompareButton);

        return $productRender;
    }
}
