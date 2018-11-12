<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for checking that product fields with directives allowed are rendered correctly
 */
class ProductWithDescriptionDirectivesTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testHtmlDirectivesRendered()
    {
        $productSku = 'simple';
        $cmsBlockId = 'fixture_block';
        $assertionCmsBlockText = 'Fixture Block Title';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setDescription('Test: {{block id="' . $cmsBlockId . '"}}');
        $product->setShortDescription('Test: {{block id="' . $cmsBlockId . '"}}');
        $productRepository->save($product);

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}}) {
        items {
            description
            short_description        
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertContains($assertionCmsBlockText, $response['products']['items'][0]['description']);
        self::assertNotContains('{{block id', $response['products']['items'][0]['description']);
        self::assertContains($assertionCmsBlockText, $response['products']['items'][0]['short_description']);
        self::assertNotContains('{{block id', $response['products']['items'][0]['short_description']);
    }
}
