<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Framework\Exception\CouldNotSaveException;
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

    /** @var  Link */
    private $productWebsiteLink;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productWebsiteLink = $this->objectManager->get(Link::class);
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
        $this->assertEquals(
            [$defaultWebsiteId, $secondWebsiteId],
            $this->productWebsiteLink->getWebsiteIdsByProductId($product->getId())
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @return void
     */
    public function testUnassignProductFromWebsite(): void
    {
        $secondWebsiteId = $this->websiteRepository->get('test')->getId();
        $product = $this->updateProductWebsites('simple-on-two-websites', [$secondWebsiteId]);
        $this->assertEquals([$secondWebsiteId], $this->productWebsiteLink->getWebsiteIdsByProductId($product->getId()));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testAssignNonExistingWebsite(): void
    {
        $messageFormat = 'The product was unable to be saved. Please try again.';
        $nonExistingWebsiteId = 921564;
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage((string)__($messageFormat));
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
}
