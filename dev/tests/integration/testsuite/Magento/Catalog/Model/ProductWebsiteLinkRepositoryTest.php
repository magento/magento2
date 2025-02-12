<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests to check products to websites assigning.
 *
 * @see \Magento\Catalog\Model\ProductWebsiteLinkRepository
 *
 * @magentoAppIsolation enabled
 */
class ProductWebsiteLinkRepositoryTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductWebsiteLinkRepositoryInterface */
    private $productWebsiteLinkRepository;

    /** @var ProductWebsiteLinkInterfaceFactory */
    private $productWebsiteLinkFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productWebsiteLinkRepository = $this->objectManager->get(ProductWebsiteLinkRepositoryInterface::class);
        $this->productWebsiteLinkFactory = $this->objectManager->get(ProductWebsiteLinkInterfaceFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testSaveWithoutWebsiteId(): void
    {
        $productWebsiteLink = $this->productWebsiteLinkFactory->create();
        $productWebsiteLink->setSku('unique-simple-azaza');
        $this->expectException(InputException::class);
        $this->expectExceptionMessage((string)__('There are not websites for assign to product'));
        $this->productWebsiteLinkRepository->save($productWebsiteLink);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_two_websites.php
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->markTestSkipped('Blocked by MC-40250');
        $productWebsiteLink = $this->productWebsiteLinkFactory->create();
        $productWebsiteLink->setSku('unique-simple-azaza');
        $productWebsiteLink->setWebsiteId(1);
        $this->productWebsiteLinkRepository->delete($productWebsiteLink);
        $product = $this->productRepository->get('unique-simple-azaza', false, null, true);
        $this->assertEquals([$this->websiteRepository->get('second_website')->getId()], $product->getWebsiteIds());
    }
}
