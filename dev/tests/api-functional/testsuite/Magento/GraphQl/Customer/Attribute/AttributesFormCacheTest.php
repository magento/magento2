<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\PageCache\Model\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;

/**
 * Test caching for attributes form GraphQL query_CUSTOMER_REGISTER_ADDRESS.
 */
class AttributesFormCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeInterface[]
     */
    private $attributesToRemove;

    private const QUERY_CUSTOMER_REGISTER_ADDRESS = <<<QRY
{
  attributesForm(formCode: "customer_register_address") {
    items {
      code
    }
    errors {
      type
      message
    }
  }
}
QRY;

    private const QUERY_CUSTOMER_EDIT_ADDRESS = <<<QRY
{
  attributesForm(formCode: "customer_account_edit") {
    items {
      code
    }
    errors {
      type
      message
    }
  }
}
QRY;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attributesToRemove = [];
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        array_walk($this->attributesToRemove, function ($attribute) use ($eavAttributeRepo) {
            $eavAttributeRepo->delete($attribute);
        });
        parent::tearDown();
    }

    /**
     * Obtains cache ID header from response
     *
     * @param string $query
     * @return string
     */
    private function getCacheIdHeader(string $query, array $headers = []): string
    {
        $response = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            $headers
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        return $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_register_address']
            ],
            'attribute_1'
        )
    ]
    public function testAttributesFormCacheMissAndHit()
    {
        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');
        $cacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);

        /** First response should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Second response should be a HIT and attribute should be present in a cached response */
        $response = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        foreach ($response['body']['attributesForm']['items'] as $item) {
            if (in_array($attribute1->getAttributeCode(), $item)) {
                return;
            }
        }
        $this->fail(sprintf(
            "Attribute '%s' not found in query_CUSTOMER_REGISTER_ADDRESS response",
            $attribute1->getAttributeCode()
        ));
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(
            StoreGroupFixture::class,
            [
                'website_id' => '$website2.id$'
            ],
            'store_group2'
        ),
        DataFixture(
            StoreFixture::class,
            [
                'store_group_id' => '$store_group2.id$'
            ],
            'store2'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_register_address']
            ],
            'attribute_1'
        )
    ]
    public function testAttributesFormCacheMissAndHitDifferentWebsites()
    {
        /** @var StoreInterface $store2 */
        $store2 = DataFixtureStorageManager::getStorage()->get('store2');
        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');
        $cacheIdStore1 = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);

        $response = $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdStore1]
        );

        $this->assertContains($attribute1->getAttributeCode(), array_map(function ($attribute) {
            return $attribute['code'];
        }, $response['body']['attributesForm']['items']));

        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdStore1]
        );

        // obtain CacheID for Store 2 - has to be different than for Store 1:
        $cacheIdStore2 = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS, ['Store' => $store2->getCode()]);

        // First query execution for a different store should result in a cache miss, while second one should be a hit
        $response = $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [
                'Store' => $store2->getCode(),
                CacheIdCalculator::CACHE_ID_HEADER => $cacheIdStore2
            ]
        );

        $this->assertContains($attribute1->getAttributeCode(), array_map(function ($attribute) {
            return $attribute['code'];
        }, $response['body']['attributesForm']['items']));

        $response = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [
                'Store' => $store2->getCode(),
                CacheIdCalculator::CACHE_ID_HEADER => $cacheIdStore2
            ]
        );
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_register_address']
            ],
            'attribute_1'
        )
    ]
    public function testAttributesFormCacheInvalidateOnAttributeEdit()
    {
        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');

        $cacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);

        /** First response should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Second response should be a HIT */
        $response = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Modify attribute to invalidate cache */
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $attribute1->setDefaultValue("default_value");
        $eavAttributeRepo->save($attribute1);

        /** Response after the change should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Second response should be a HIT */
        $response = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );
        foreach ($response['body']['attributesForm']['items'] as $item) {
            if (in_array($attribute1->getAttributeCode(), $item)) {
                return;
            }
        }
        $this->fail(sprintf(
            "Attribute '%s' not found in query_CUSTOMER_REGISTER_ADDRESS response",
            $attribute1->getAttributeCode()
        ));
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH)
    ]
    public function testAttributesFormCacheInvalidateOnAttributeCreate()
    {
        $cacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);

        /** First response should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Second response should be a HIT */
        $response = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Create new attribute and assign it to customer_register_address */
        $attributeCreate = $this->objectManager->get(CustomerAttribute::class);
        $attribute = $attributeCreate->apply([
            'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'used_in_forms' => ['customer_register_address']
        ]);
        $this->attributesToRemove[] = $attribute;

        /** Response after the creation of new attribute should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Second response should be a HIT */
        $response = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        // verify created attribute is present in result
        foreach ($response['body']['attributesForm']['items'] as $item) {
            if (in_array($attribute->getAttributeCode(), $item)) {
                return;
            }
        }
        $this->fail(sprintf(
            "Attribute '%s' not found in QUERY_CUSTOMER_REGISTER_ADDRESS response",
            $attribute->getAttributeCode()
        ));
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_register_address']
            ],
            'attribute_1'
        )
    ]
    public function testAttributesFormCacheInvalidateOnAttributeDelete()
    {
        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');
        $cacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);

        /** First response should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Second response should be a HIT */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        /** Delete attribute to invalidate cache */
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $deletedAttributeCode = $attribute1->getAttributeCode();
        $eavAttributeRepo->delete($attribute1);

        /** First response should be a MISS and attribute should NOT be present in a cached response */
        $response = $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        foreach ($response['body']['attributesForm']['items'] as $item) {
            if (in_array($deletedAttributeCode, $item)) {
                $this->fail(sprintf(
                    "Deleted attribute '%s' found in cached query_CUSTOMER_REGISTER_ADDRESS response",
                    $deletedAttributeCode
                ));
            }
        }

        /** Second response should be a HIT */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            ],
            'attribute'
        )
    ]
    public function testAttributesFormCacheInvalidateOnAttributeAssignToForm()
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $queryEditCacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);
        $queryRegisterCacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_REGISTER_ADDRESS);

        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
        /** Second response should be a HIT*/
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Assign $attribute to the 'customer_account_edit' form */
        $attribute->setData('used_in_forms', ['customer_account_edit']);
        $eavAttributeRepo->save($attribute);

        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Non-affected "customer_register_address" form -> MISS, then cached and HIT */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );

        /** Add $attribute to the 'customer_register_address' form */
        $attribute->setData('used_in_forms', ['customer_account_edit', 'customer_register_address']);
        $eavAttributeRepo->save($attribute);

        /** 'customer_register_address' form should be invalidated first now */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );

        /** Remove $attribute from the 'customer_account_edit' form */
        $attribute->setData('used_in_forms', ['customer_register_address']);
        $eavAttributeRepo->save($attribute);

        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );

        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );

        /** Remove $attribute from remaining form(s) */
        $attribute->setData('used_in_forms', []);
        $eavAttributeRepo->save($attribute);

        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );

        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => [
                    'customer_register_address',
                    'customer_account_edit'
                ]
            ],
            'shared_attribute'
        )
    ]
    public function testAttributesFormCacheInvalidateOnDeletedSharedAttribute()
    {
        /** @var AttributeInterface $sharedAttribute */
        $sharedAttribute = DataFixtureStorageManager::getStorage()->get('shared_attribute');
        $queryEditCacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);
        $queryRegisterCacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_REGISTER_ADDRESS);

        /** First response should be a MISS from both queries */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        // /** Second response should be a HIT from both queries */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Delete attribute to invalidate both cached queries */
        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);
        $deletedAttributeCode = $sharedAttribute->getAttributeCode();
        $eavAttributeRepo->delete($sharedAttribute);

        /** First response after deleting should be a MISS from both queries */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Second response should be a HIT from both queries as they are both cached back */
        $responseRegisterAddress = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $responseEditAddress = $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        // verify created attribute is NOT present in results
        foreach ($responseRegisterAddress['body']['attributesForm']['items'] as $item) {
            if (in_array($deletedAttributeCode, $item)) {
                $this->fail(
                    sprintf(
                        "Attribute '%s' found in QUERY_CUSTOMER_REGISTER_ADDRESS response",
                        $deletedAttributeCode
                    )
                );
            }
        }

        foreach ($responseEditAddress['body']['attributesForm']['items'] as $item) {
            if (in_array($deletedAttributeCode, $item)) {
                $this->fail(
                    sprintf(
                        "Attribute '%s' found in QUERY_CUSTOMER_EDIT_ADDRESS response",
                        $deletedAttributeCode
                    )
                );
            }
        }
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => [
                    'customer_account_edit'
                ]
            ],
            'non_shared_attribute_2'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => [
                    'customer_register_address'
                ]
            ],
            'non_shared_attribute_1'
        )
    ]
    public function testAttributesFormCacheInvalidateOnDeletedNonSharedAttribute()
    {
        /** @var AttributeInterface $nonSharedAttribute1 */
        $nonSharedAttribute1 = DataFixtureStorageManager::getStorage()->get('non_shared_attribute_1');
        /** @var AttributeInterface $nonSharedAttribute2 */
        $nonSharedAttribute2 = DataFixtureStorageManager::getStorage()->get('non_shared_attribute_2');
        $queryEditCacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_EDIT_ADDRESS);
        $queryRegisterCacheId = $this->getCacheIdHeader(self::QUERY_CUSTOMER_REGISTER_ADDRESS);

        $eavAttributeRepo = $this->objectManager->get(AttributeRepository::class);

        /** First response should be a MISS from both queries */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Second response should be a HIT from all queries */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Delete nonSharedAttribute1 to invalidate cache of 'customer_register_address' ONLY*/
        $eavAttributeRepo->delete($nonSharedAttribute1);

        /** First response from QUERY_CUSTOMER_REGISTER_ADDRESS after deleting $nonSharedAttribute1 should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        /** other cached queries should not be affected */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Second response should be a HIT from all queries */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );

        /** Delete nonSharedAttribute2 to invalidate cache of 'customer_account_edit' ONLY*/
        $eavAttributeRepo->delete($nonSharedAttribute2);

        /** First response from QUERY_CUSTOMER_EDIT_ADDRESS after deleting $nonSharedAttribute2 should be a MISS */
        $this->assertCacheMissAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
        /** other cached queries should not be affected */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );

        /** Second response should be a HIT from all queries */
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_REGISTER_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryRegisterCacheId]
        );
        $this->assertCacheHitAndReturnResponse(
            self::QUERY_CUSTOMER_EDIT_ADDRESS,
            [CacheIdCalculator::CACHE_ID_HEADER => $queryEditCacheId]
        );
    }
}
