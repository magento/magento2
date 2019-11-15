<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\WebsiteFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks product websites attribute save behaviour
 *
 * @magentoDbIsolation enabled
 */
class UpdateProductWebsiteTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var  WebsiteFactory */
    private $websiteProductsResourceFactory;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->websiteProductsResourceFactory = $this->objectManager->get(WebsiteFactory::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testAssignProductToWebsite(): void
    {
        $defaultWebsiteId = $this->websiteRepository->get('base')->getId();
        $secondWebsiteId = $this->websiteRepository->get('test')->getId();
        $product = $this->updateProductWebsites('simple2', [$defaultWebsiteId, $secondWebsiteId]);
        $this->assertProductWebsites((int)$product->getId(), [$defaultWebsiteId, $secondWebsiteId]);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @return void
     */
    public function testUnassignProductFromWebsite(): void
    {
        $product = $this->productRepository->get('simple-on-two-websites');
        $secondWebsiteId = $this->websiteRepository->get('test')->getId();
        $product->setWebsiteIds([$secondWebsiteId]);
        $product = $this->productRepository->save($product);
        $this->assertProductWebsites((int)$product->getId(), [$secondWebsiteId]);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testAssignNonExistingWebsite(): void
    {
        $messageFormat = 'The website with id %s that was requested wasn\'t found. Verify the website and try again.';
        $nonExistingWebsiteId = 921564;
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage((string)__(sprintf($messageFormat, $nonExistingWebsiteId)));
        $this->updateProductWebsites('simple2', [$nonExistingWebsiteId]);
    }

    /**
     * Update product websites attribute
     *
     * @param string $productSku
     * @param array $websiteIds
     * @return ProductInterface
     */
    private function updateProductWebsites(string $productSku, array $websiteIds): ProductInterface
    {
        $product = $this->productRepository->get($productSku);
        $product->setWebsiteIds($websiteIds);

        return $this->productRepository->save($product);
    }

    /**
     * Assert that websites attribute was correctly saved
     *
     * @param int $productId
     * @param array $expectedData
     * @return void
     */
    private function assertProductWebsites(int $productId, array $expectedData): void
    {
        $websiteResource = $this->websiteProductsResourceFactory->create();
        $this->assertEquals($expectedData, $websiteResource->getWebsites([$productId])[$productId]);
    }
}
