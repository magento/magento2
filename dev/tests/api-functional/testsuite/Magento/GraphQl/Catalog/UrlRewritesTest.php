<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDTO;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test of getting URL rewrites data from products
 */
class UrlRewritesTest extends GraphQlAbstract
{
    /**
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testProductWithNoCategoriesAssigned()
    {
        $productSku = 'virtual-product';
        $query
            = <<<QUERY
{
    products (filter: {sku: {eq: "{$productSku}"}}) {
        items {
            name,
            sku,
            description {
                html
            }
            url_rewrites {
              url,
              parameters {
                name,
                value
              }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('virtual-product', false, null, true);

        $storeId = ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore()->getId();
        $urlFinder = ObjectManager::getInstance()->get(UrlFinderInterface::class);
        $entityType = ObjectManager::getInstance()->create(EavConfig::class)->getEntityType('catalog_product');

        $entityTypeCode = $entityType->getEntityTypeCode();
        if ($entityTypeCode === 'catalog_product') {
            $entityTypeCode = 'product';
        }

        $rewritesCollection = $urlFinder->findAllByData(
            [
                UrlRewriteDTO::ENTITY_ID => $product->getId(),
                UrlRewriteDTO::ENTITY_TYPE => $entityTypeCode,
                UrlRewriteDTO::STORE_ID => $storeId
            ]
        );

        /* There should be only one rewrite */
        /** @var UrlRewriteDTO $urlRewrite */
        $urlRewrite = current($rewritesCollection);

        $this->assertArrayHasKey('url_rewrites', $response['products']['items'][0]);
        $this->assertCount(1, $response['products']['items'][0]['url_rewrites']);

        $this->assertResponseFields(
            $response['products']['items'][0]['url_rewrites'][0],
            [
                "url" => $urlRewrite->getRequestPath(),
                "parameters" => $this->getUrlParameters($urlRewrite->getTargetPath())
            ]
        );
    }

    /**
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testProductWithOneCategoryAssigned()
    {
        $productSku = 'simple';
        $query
            = <<<QUERY
{
    products (filter: {sku: {eq: "{$productSku}"}}) {
        items {
            name,
            sku,
            description {
                html
            }
            url_rewrites {
              url,
              parameters {
                name,
                value
              }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple', false, null, true);

        $storeId = ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore()->getId();
        $urlFinder = ObjectManager::getInstance()->get(UrlFinderInterface::class);
        $entityType = ObjectManager::getInstance()->create(EavConfig::class)->getEntityType('catalog_product');

        $entityTypeCode = $entityType->getEntityTypeCode();
        if ($entityTypeCode === 'catalog_product') {
            $entityTypeCode = 'product';
        }

        $rewritesCollection = $urlFinder->findAllByData(
            [
                UrlRewriteDTO::ENTITY_ID => $product->getId(),
                UrlRewriteDTO::ENTITY_TYPE => $entityTypeCode,
                UrlRewriteDTO::STORE_ID => $storeId
            ]
        );

        $rewritesCount = count($rewritesCollection);
        $this->assertArrayHasKey('url_rewrites', $response['products']['items'][0]);
        $this->assertCount(1, $response['products']['items'][0]['url_rewrites']);
        $this->assertCount($rewritesCount, $response['products']['items'][0]['url_rewrites']);

        for ($index = 0; $index < $rewritesCount; $index++) {
            $urlRewrite = $rewritesCollection[$index];
            $this->assertResponseFields(
                $response['products']['items'][0]['url_rewrites'][$index],
                [
                    "url" => $urlRewrite->getRequestPath(),
                    "parameters" => $this->getUrlParameters($urlRewrite->getTargetPath())
                ]
            );
        }
    }

    /**
     * Parses target path and extracts parameters
     *
     * @param string $targetPath
     * @return array
     */
    private function getUrlParameters(string $targetPath): array
    {
        $urlParameters = [];
        $targetPathParts = explode('/', trim($targetPath, '/'));
        $count = count($targetPathParts) - 1;
        //phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($index = 3; $index < $count; $index += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$index],
                'value' => $targetPathParts[$index + 1]
            ];
        }

        return $urlParameters;
    }
}
