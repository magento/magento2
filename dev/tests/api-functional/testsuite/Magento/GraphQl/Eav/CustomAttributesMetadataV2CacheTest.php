<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Eav;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\PageCache\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;

/**
 * Test caching for custom attribute metadata GraphQL query.
 */
class CustomAttributesMetadataV2CacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->attributeRepository = Bootstrap::getObjectManager()->get(AttributeRepository::class);
        parent::setUp();
    }

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'text'
            ],
            'attribute'
        ),
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH)
    ]
    public function testCacheHitMiss(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $query = $this->getAttributeQuery($attribute->getAttributeCode(), "customer");
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $assertionMap = [
            ['response_field' => 'code', 'expected_value' => $attribute->getAttributeCode()],
            ['response_field' => 'entity_type', 'expected_value' => 'CUSTOMER'],
            ['response_field' => 'frontend_input', 'expected_value' => 'TEXT']
        ];
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);
        $response = $this->assertCacheHitAndReturnResponse($query, []);
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'text'
            ],
            'attribute'
        ),
    ]
    public function testCacheMissAndHitDifferentStores(): void
    {
        /** @var StoreInterface $store2 */
        $store2 = DataFixtureStorageManager::getStorage()->get('store2');

        /** @var AttributeMetadataInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $query = $this->getAttributeQuery($attribute->getAttributeCode(), "customer");
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $assertionMap = [
            ['response_field' => 'code', 'expected_value' => $attribute->getAttributeCode()],
            ['response_field' => 'entity_type', 'expected_value' => 'CUSTOMER'],
            ['response_field' => 'frontend_input', 'expected_value' => 'TEXT']
        ];
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);
        $response = $this->assertCacheHitAndReturnResponse($query, []);
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);

        // First query execution for a different store should result in a cache miss, while second one should be a hit
        $response = $this->assertCacheMissAndReturnResponse($query, ['Store' => $store2->getCode()]);
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);
        $response = $this->assertCacheHitAndReturnResponse($query, ['Store' => $store2->getCode()]);
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);
    }

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'text'
            ],
            'attribute_1'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean'
            ],
            'attribute_2'
        ),
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH)
    ]
    public function testCacheInvalidation(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute_1');

        /** @var AttributeInterface $attribute2 */
        $attribute2 = DataFixtureStorageManager::getStorage()->get('attribute_2');

        $query = $this->getAttributeQuery($attribute->getAttributeCode(), "customer");
        // check cache missed on first query
        $this->assertCacheMissAndReturnResponse($query, []);
        // assert cache hit on second query
        $this->assertCacheHitAndReturnResponse($query, []);

        $attribute->setIsRequired(true);
        $this->attributeRepository->save($attribute);
        // assert cache miss after changes
        $this->assertCacheMissAndReturnResponse($query, []);

        $attribute2->setIsRequired(true);
        $this->attributeRepository->save($attribute2);

        // assert cache hits on second query after changes, and cache is not invalidated when another entity changed
        $this->assertCacheHitAndReturnResponse($query, []);
    }

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'text'
            ],
            'attribute'
        ),
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH)
    ]
    public function testCacheInvalidationOnAttributeDelete()
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');
        $attributeCode = $attribute->getAttributeCode();

        $query = $this->getAttributeQuery($attributeCode, "customer");

        // check cache missed on first query
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $assertionMap = [
            ['response_field' => 'code', 'expected_value' => $attributeCode],
            ['response_field' => 'entity_type', 'expected_value' => 'CUSTOMER'],
            ['response_field' => 'frontend_input', 'expected_value' => 'TEXT']
        ];
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);

        // assert cache hit on second query
        $response = $this->assertCacheHitAndReturnResponse($query, []);
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['items'][0], $assertionMap);

        $this->attributeRepository->delete($attribute);
        $assertionMap = [
            ['response_field' => 'type', 'expected_value' => 'ATTRIBUTE_NOT_FOUND'],
            ['response_field' => 'message', 'expected_value' => sprintf(
                'Attribute code "%s" could not be found.',
                $attributeCode
            )]
        ];
        $response = $this->assertCacheMissAndReturnResponse($query, []);
        $this->assertResponseFields($response['body']['customAttributeMetadataV2']['errors'][0], $assertionMap);
    }

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'text'
            ],
            'attribute'
        ),
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH)
    ]
    public function testCacheMissingAttributeParam(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $query = $this->getAttributeQueryNoCode("customer");
        // check cache missed on each query
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "Missing attribute_code for the input entity_type: customer."
        );
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            "Missing attribute_code for the input entity_type: customer."
        );

        $query = $this->getAttributeQueryNoEntityType($attribute->getAttributeCode());
        // check cache missed on each query
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            sprintf("Missing entity_type for the input attribute_code: %s.", $attribute->getAttributeCode())
        );
        $this->assertQueryResultIsCacheMissWithError(
            $query,
            sprintf("Missing entity_type for the input attribute_code: %s.", $attribute->getAttributeCode())
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
            'MISS',
            $caughtException->getResponseHeaders()['X-Magento-Cache-Debug']
        );
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
  customAttributeMetadataV2(attributes: [{attribute_code:"{$code}", entity_type:"{$entityType}"}]) {
    items {
      code
      label
      entity_type
      frontend_input
      is_required
      default_value
      is_unique
      options {
        label
        value
      }
    }
    errors {
      type
      message
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
