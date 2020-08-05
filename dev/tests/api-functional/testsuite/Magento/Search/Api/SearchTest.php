<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SearchTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'searchV1';
    const RESOURCE_PATH = '/V1/search/';

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $productSku = 'simple';

        $objectManager = Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->product = $productRepository->get($productSku);
    }

    /**
     * Tests that webapi call returns response when search criteria is valid.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     */
    public function testExistingProductSearch(): void
    {
        $productName = $this->product->getName();

        $searchCriteria = $this->buildSearchCriteria($productName);
        $serviceInfo = $this->buildServiceInfo($searchCriteria);

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        self::assertArrayHasKey('search_criteria', $response);
        self::assertArrayHasKey('items', $response);
        self::assertGreaterThan(1, count($response['items']));
        self::assertGreaterThan(0, $response['items'][0]['id']);
    }

    /**
     * Tests that response is empty if invalid data is provided.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     */
    public function testNonExistentProductSearch(): void
    {
        $searchCriteria = $this->buildSearchCriteria('nonExistentProduct');
        $serviceInfo = $this->buildServiceInfo($searchCriteria);

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        self::assertArrayHasKey('search_criteria', $response);
        self::assertArrayHasKey('items', $response);
        self::assertCount(0, $response['items']);
    }

    /**
     * @param string $productName
     * @return array
     */
    private function buildSearchCriteria(string $productName): array
    {
        return [
            'searchCriteria' => [
                'request_name' => 'quick_search_container',
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'search_term',
                                'value' => $productName,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $searchCriteria
     * @return array
     */
    private function buildServiceInfo(array $searchCriteria): array
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Search'
            ]
        ];
    }
}
