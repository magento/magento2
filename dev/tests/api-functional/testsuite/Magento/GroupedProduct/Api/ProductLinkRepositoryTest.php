<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Indexer\Model\Config;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Webapi\Rest\Request;

class ProductLinkRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductLinkRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';
    const SERVICE_NAME_SEARCH = 'searchV1';
    const RESOURCE_PATH_SEARCH = '/V1/search/';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    private $indexersState;

    /**
     * @var mixed
     */
    private $indexerRegistry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->indexerRegistry = $this->objectManager->get(IndexerRegistry::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testSave(): void
    {
        $productSku = 'grouped-product';
        $linkType = 'associated';
        $productData = [
            'sku' => $productSku,
            'link_type' => $linkType,
            'linked_product_type' => 'simple',
            'linked_product_sku' => 'simple-1',
            'position' => 3,
            'extension_attributes' => [
                'qty' =>  (float) 300.0000,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/links',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['entity' => $productData]);

        /** @var \Magento\Catalog\Api\ProductLinkManagementInterface $linkManagement */
        $linkManagement = $this->objectManager->get(\Magento\Catalog\Api\ProductLinkManagementInterface::class);
        $actual = $linkManagement->getLinkedItemsByType($productSku, $linkType);
        array_walk($actual, function (&$item) {
            $item = $item->__toArray();
        });
        $this->assertEquals($productData, $actual[2]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testLinkWithScheduledIndex(): void
    {
        $this->setIndexScheduled();
        $productSkuGrouped = 'grouped-product';
        $productSimple = 'simple-1';
        $linkType = 'associated';
        $productData = [
            'sku' => $productSkuGrouped,
            'link_type' => $linkType,
            'linked_product_type' => 'simple',
            'linked_product_sku' => $productSimple,
            'position' => 3,
            'extension_attributes' => [
                'qty' =>  (float) 300.0000,
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSkuGrouped . '/links',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['entity' => $productData]);

        $searchCriteria = $this->buildSearchCriteria($productSimple);
        $serviceInfo = $this->buildSearchServiceInfo($searchCriteria);
        $response = $this->_webApiCall($serviceInfo, $searchCriteria);
        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('items', $response);
        $this->assertGreaterThan(1, count($response['items']));
        $this->assertGreaterThan(0, $response['items'][0]['id']);
        $this->restoreIndexMode();
    }

    /**
     * @param string $productSku
     * @return array
     */
    private function buildSearchCriteria(string $productSku): array
    {
        return [
            'searchCriteria' => [
                'request_name' => 'quick_search_container',
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'search_term',
                                'value' => $productSku,
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
    private function buildSearchServiceInfo(array $searchCriteria): array
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_SEARCH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_SEARCH,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME_SEARCH . 'Search'
            ]
        ];
    }

    private function setIndexScheduled(): void
    {
        $indexerListIds = $this->objectManager->get(Config::class)->getIndexers();
        foreach ($indexerListIds as $indexerId) {
            $indexer = $this->indexerRegistry->get($indexerId['indexer_id']);
            $this->indexersState[$indexerId['indexer_id']] = $indexer->isScheduled();
            $indexer->setScheduled(true);
        }
    }

    private function restoreIndexMode(): void
    {
        foreach ($this->indexersState as $indexerId => $state) {
            $this->indexerRegistry->get($indexerId)->setScheduled($state);
        }
    }
}
