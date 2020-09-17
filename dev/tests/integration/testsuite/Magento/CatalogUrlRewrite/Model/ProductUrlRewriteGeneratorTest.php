<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class ProductUrlRewriteGeneratorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testGenerateWithSpecificCategoryUrlKey()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('p002');
        // set global store
        $product->setStoreId(0);

        /** @var ProductUrlRewriteGenerator $generator */
        $generator = $this->objectManager->get(ProductUrlRewriteGenerator::class);
        $urls = $generator->generate($product);

        $actualUrls = array_map(
            function ($url) {
                /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url */
                return $url->getRequestPath();
            },
            $urls
        );

        self::assertTrue(in_array('p002.html', $actualUrls));
        self::assertTrue(in_array('cat-1/p002.html', $actualUrls));
        self::assertTrue(in_array('cat-1-2/p002.html', $actualUrls));
    }
}
