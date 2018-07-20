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

        $query
            = <<<QUERY
{
    products (search:"Virtual Product") {
        items {
            name,
            sku,
            description,
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

        $urlFinder = ObjectManager::getInstance()->get(UrlFinderInterface::class);

        $rewritesCollection = $urlFinder->findAllByData([UrlRewriteDTO::ENTITY_ID => $product->getId()]);

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

        $query
            = <<<QUERY
{
    products (search:"Simple Product") {
        items {
            name,
            sku,
            description,
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

        $urlFinder = ObjectManager::getInstance()->get(UrlFinderInterface::class);

        $rewritesCollection = $urlFinder->findAllByData([UrlRewriteDTO::ENTITY_ID => $product->getId()]);
        $rewritesCount = count($rewritesCollection);

        $this->assertArrayHasKey('url_rewrites', $response['products']['items'][0]);
        $this->assertCount($rewritesCount, $response['products']['items'][0]['url_rewrites']);

        for ($i = 0; $i < $rewritesCount; $i++) {
            $urlRewrite = $rewritesCollection[$i];
            $this->assertResponseFields(
                $response['products']['items'][0]['url_rewrites'][$i],
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

        for ($i = 3; ($i < sizeof($targetPathParts) - 1); $i += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$i],
                'value' => $targetPathParts[$i + 1]
            ];
        }

        return $urlParameters;
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap
     */
    private function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            self::assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            self::assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }
}
