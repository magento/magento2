<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Eav;

use Magento\Eav\Model\AttributeRepository;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

/**
 * Test caching for custom attribute metadata GraphQL query.
 */
class CustomAttributesMetadataCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        parent::setUp();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     *
     * @return void
     */
    public function testCacheHitMiss()
    {
        $query = $this->getAttributeQuery("dropdown_attribute", "catalog_product");
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $this->assertResponseFields(
            $response['body']['customAttributeMetadata']['items'][0],
            [
                'attribute_code' => 'dropdown_attribute',
                'attribute_type' => 'String',
                'entity_type' => 'catalog_product',
                'input_type' => 'select',
            ]
        );
        $response = $this->assertCacheHitAndReturnResponse($query, []);
        $this->assertResponseFields(
            $response['body']['customAttributeMetadata']['items'][0],
            [
                'attribute_code' => 'dropdown_attribute',
                'attribute_type' => 'String',
                'entity_type' => 'catalog_product',
                'input_type' => 'select',
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     *
     * @return void
     */
    public function testCacheDifferentStores()
    {
        $query = $this->getAttributeQuery("dropdown_attribute", "catalog_product");
        /** @var AttributeRepository $eavAttributeRepo */
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        /** @var StoreRepository $storeRepo */
        $storeRepo = $this->objectManager->get(StoreRepository::class);

        $stores = $storeRepo->getList();
        $attribute = $eavAttributeRepo->get("catalog_product", "dropdown_attribute");
        $options = $attribute->getOptions();

        //prepare unique option values per-store
        $storeOptions = [];
        foreach ($options as $option) {
            $optionValues = $option->getData();
            if (!empty($optionValues['value'])) {
                $storeOptions['value'][$optionValues['value']] = [];
                foreach ($stores as $store) {
                    $storeOptions['value'][$optionValues['value']][$store->getId()] = $store->getCode()
                        . '_'
                        . $optionValues['label'];
                }
            }
        }
        //save attribute with new option values
        $attribute->addData(['option' => $storeOptions]);
        $eavAttributeRepo->save($attribute);

        // get attribute metadata for test store and assert it missed the cache
        $response = $this->assertCacheMissAndReturnResponse($query, ['Store' => 'test']);
        $options = $response['body']['customAttributeMetadata']['items'][0]['attribute_options'];
        $this->assertOptionValuesPerStore($storeOptions, 'test', $stores, $options);

        // get attribute metadata for test store again and assert it has hit the cache
        $response = $this->assertCacheHitAndReturnResponse($query, ['Store' => 'test']);
        $options = $response['body']['customAttributeMetadata']['items'][0]['attribute_options'];
        $this->assertOptionValuesPerStore($storeOptions, 'test', $stores, $options);

        $response = $this->assertCacheMissAndReturnResponse($query, ['Store' => 'default']);
        $options = $response['body']['customAttributeMetadata']['items'][0]['attribute_options'];
        $this->assertOptionValuesPerStore($storeOptions, 'default', $stores, $options);

        $response = $this->assertCacheHitAndReturnResponse($query, ['Store' => 'default']);
        $options = $response['body']['customAttributeMetadata']['items'][0]['attribute_options'];
        $this->assertOptionValuesPerStore($storeOptions, 'default', $stores, $options);
    }

    /**
     * Assert attribute option labels for each store provided.
     *
     * @param array $storeOptions
     * @param string $storeCode
     * @param \Magento\Store\Api\Data\StoreInterface[] $stores
     * @param array $options
     *
     * @return void
     */
    private function assertOptionValuesPerStore($storeOptions, $storeCode, $stores, $options)
    {
        foreach ($options as $option) {
            $this->assertEquals(
                $storeOptions['value'][$option['value']][$stores[$storeCode]->getId()],
                $option['label']
            );
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     *
     * @return void
     */
    public function testCacheInvalidation()
    {
        $query = $this->getAttributeQuery("dropdown_attribute", "catalog_product");
        // check cache missed on first query
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $this->assertResponseFields(
            $response['body']['customAttributeMetadata']['items'][0],
            [
                'attribute_code' => 'dropdown_attribute',
                'attribute_type' => 'String',
                'entity_type' => 'catalog_product',
                'input_type' => 'select',
            ]
        );
        // assert cache hit on second query
        $this->assertCacheHitAndReturnResponse($query, []);
        /** @var AttributeRepository $eavAttributeRepo */
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $attribute = $eavAttributeRepo->get("catalog_product", "dropdown_attribute");
        $attribute->setIsRequired(1);
        $eavAttributeRepo->save($attribute);
        // assert cache miss after changes
        $this->assertCacheMissAndReturnResponse($query, []);
        // assert cache hits on second query after changes
        $response = $this->assertCacheHitAndReturnResponse($query, []);
        $this->assertResponseFields(
            $response['body']['customAttributeMetadata']['items'][0],
            [
                'attribute_code' => 'dropdown_attribute',
                'attribute_type' => 'String',
                'entity_type' => 'catalog_product',
                'input_type' => 'select',
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     *
     * @return void
     */
    public function testCacheInvalidationOnAttributeDelete()
    {
        $query = $this->getAttributeQuery("dropdown_attribute", "catalog_product");
        // check cache missed on first query
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $this->assertResponseFields(
            $response['body']['customAttributeMetadata']['items'][0],
            [
                'attribute_code' => 'dropdown_attribute',
                'attribute_type' => 'String',
                'entity_type' => 'catalog_product',
                'input_type' => 'select',
            ]
        );
        // assert cache hit on second query
        $this->assertCacheHitAndReturnResponse($query, []);
        /** @var AttributeRepository $eavAttributeRepo */
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $attribute = $eavAttributeRepo->get("catalog_product", "dropdown_attribute");
        $eavAttributeRepo->delete($attribute);
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "GraphQL response contains errors: Internal server error"
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     *
     * @return void
     */
    public function testCacheMissingAttributeParam()
    {
        $query = $this->getAttributeQueryNoCode("catalog_product");
        // check cache missed on each query
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "Missing attribute_code for the input entity_type: catalog_product."
        );
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "Missing attribute_code for the input entity_type: catalog_product."
        );

        $query = $this->getAttributeQueryNoEntityType("dropdown_attribute");
        // check cache missed on each query
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "Missing entity_type for the input attribute_code: dropdown_attribute."
        );
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "Missing entity_type for the input attribute_code: dropdown_attribute."
        );
    }

    /**
     * Assert that query produces an error and the cache is missed.
     *
     * @param string $query
     * @param string $expectedError
     * @return void
     * @throws \Exception
     */
    private function assertQueryResultIsCacheMissWithError(string $query, string $expectedError)
    {
        $caughtException = null;
        try {
            // query for response, expect response to be present in exception
            $this->graphQlQueryWithResponseHeaders($query, []);
        } catch (ResponseContainsErrorsException $exception) {
            $caughtException = $exception;
        }
        $this->assertInstanceOf(
            ResponseContainsErrorsException::class,
            $caughtException
        );
        // cannot use expectException because need to assert the headers
        $this->assertStringContainsString(
            $expectedError,
            $caughtException->getMessage()
        );
        // assert that it's a miss
        $this->assertEquals(
            $caughtException->getResponseHeaders()['X-Magento-Cache-Debug'],
            'MISS'
        );
    }

    /**
     * Test cache invalidation when queried for attribute data of different entity types.
     * Required for GraphQL FPC use-case since there is no attribute ID provided in the result.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     *
     * @return void
     */
    public function testCacheInvalidationMultiEntitySameCode()
    {
        $queryProduct = $this->getAttributeQuery("name", "catalog_product");
        $queryCategory = $this->getAttributeQuery("name", "catalog_category");
        // precache both product and category response
        $this->assertCacheMissAndReturnResponse($queryProduct, []);
        $this->assertCacheMissAndReturnResponse($queryCategory, []);
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $attribute = $eavAttributeRepo->get("catalog_product", "name");
        $eavAttributeRepo->save($attribute);
        // assert that product is invalidated for the same code but category is not touched
        $this->assertCacheMissAndReturnResponse($queryProduct, []);
        $this->assertCacheHitAndReturnResponse($queryCategory, []);
    }

    /**
     * Prepare and return GraphQL query for given entity type and code.
     *
     * @param string $code
     * @param string $entityType
     * @return string
     */
    private function getAttributeQuery(string $code, string $entityType) : string
    {
        return <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"{$code}",
      entity_type:"{$entityType}"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type
      input_type
      attribute_options{
        label
        value
      }
    }
  }
 }
QUERY;
    }

    /**
     * Prepare and return GraphQL query for given entity type with no code.
     *
     * @param string $entityType
     *
     * @return string
     */
    private function getAttributeQueryNoCode(string $entityType) : string
    {
        return <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      entity_type:"{$entityType}"
    }
  ]
  )
  {
    items
    {
      attribute_code
      entity_type
    }
  }
 }
QUERY;
    }

    /**
     * Prepare and return GraphQL query for given code with no entity type.
     *
     * @param string $code
     *
     * @return string
     */
    private function getAttributeQueryNoEntityType(string $code) : string
    {
        return <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"{$code}"
    }
  ]
  )
  {
    items
    {
      attribute_code
      entity_type
    }
  }
 }
QUERY;
    }
}
