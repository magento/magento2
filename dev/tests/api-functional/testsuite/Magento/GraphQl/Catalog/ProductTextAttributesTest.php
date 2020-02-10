<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductTextAttributesTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()::getInstance()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductTextAttributes()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            description {
                html
            }
            short_description {
                html
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertEquals(
            $productSku,
            $response['products']['items'][0]['sku']
        );
        $this->assertEquals(
            'Short description',
            $response['products']['items'][0]['short_description']['html']
        );
        $this->assertEquals(
            'Description with <b>html tag</b>',
            $response['products']['items'][0]['description']['html']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     */
    public function testProductWithoutFilledTextAttributes()
    {
        $productSku = 'virtual-product';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            description {
                html
            }
            short_description {
                html
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertEquals(
            $productSku,
            $response['products']['items'][0]['sku']
        );
        $this->assertEquals(
            '',
            $response['products']['items'][0]['short_description']['html']
        );
        $this->assertEquals(
            '',
            $response['products']['items'][0]['description']['html']
        );
    }

    /**
     * Test for checking that product fields with directives allowed are rendered correctly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testHtmlDirectivesRendering()
    {
        $productSku = 'simple';
        $cmsBlockId = 'fixture_block';
        $assertionCmsBlockText = 'Fixture Block Title';

        $product = $this->productRepository->get($productSku, false, null, true);
        $product->setDescription('Test: {{block id="' . $cmsBlockId . '"}}');
        $product->setShortDescription('Test: {{block id="' . $cmsBlockId . '"}}');
        $this->productRepository->save($product);

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}}) {
        items {
            description {
                html
            }
            short_description {
                html
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertContains($assertionCmsBlockText, $response['products']['items'][0]['description']['html']);
        self::assertNotContains('{{block id', $response['products']['items'][0]['description']['html']);
        self::assertContains($assertionCmsBlockText, $response['products']['items'][0]['short_description']['html']);
        self::assertNotContains('{{block id', $response['products']['items'][0]['short_description']['html']);
    }
}
