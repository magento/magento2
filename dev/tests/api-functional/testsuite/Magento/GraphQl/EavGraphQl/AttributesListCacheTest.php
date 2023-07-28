<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\EavGraphQl;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\AttributeFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\PageCache\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test caching for attributes list GraphQL query.
 */
class AttributesListCacheTest extends GraphQLPageCacheAbstract
{
    private const QUERY = <<<QRY
        {
            attributesList(entityType: CUSTOMER) {
                items {
                    uid
                    code
                }
                errors {
                    type
                    message
                }
            }
        }
QRY;

    private const QUERY_ADDRESS = <<<QRY
        {
            attributesList(entityType: CUSTOMER_ADDRESS) {
                items {
                    uid
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
     * @var AttributeRepository
     */
    private $eavAttributeRepo;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->eavAttributeRepo = Bootstrap::getObjectManager()->get(AttributeRepository::class);
        /** @var AttributeFactory $attributeFactory */
        $this->attributeFactory = Bootstrap::getObjectManager()->create(AttributeFactory::class);
        parent::setUp();
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'customer_attribute_0'
        ),
    ]
    public function testAttributesListCacheMissAndHit()
    {
        /** @var AttributeInterface $attribute0 */
        $attribute0 = DataFixtureStorageManager::getStorage()->get('customer_attribute_0');

        $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $response = $this->assertCacheHitAndReturnResponse(self::QUERY, []);

        $attribute = end($response['body']['attributesList']['items']);
        $this->assertEquals($attribute0->getAttributeCode(), $attribute['code']);

        // Modify an attribute present in the response of the previous query to check cache invalidation
        $attribute0->setAttributeCode($attribute0->getAttributeCode() . '_modified');
        $this->eavAttributeRepo->save($attribute0);

        // First query execution should result in a cache miss, while second one should be a cache hit
        $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $this->assertCacheHitAndReturnResponse(self::QUERY, []);
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
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'customer_attribute_0'
        ),
    ]
    public function testAttributesListCacheMissAndHitDifferentStores()
    {
        /** @var StoreInterface $store2 */
        $store2 = DataFixtureStorageManager::getStorage()->get('store2');

        /** @var AttributeInterface $attribute0 */
        $attribute0 = DataFixtureStorageManager::getStorage()->get('customer_attribute_0');

        $response = $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $attribute = end($response['body']['attributesList']['items']);
        $this->assertEquals($attribute0->getAttributeCode(), $attribute['code']);

        $this->assertCacheHitAndReturnResponse(self::QUERY, []);

        // First query execution for a different store should result in a cache miss, while second one should be a hit
        $response = $this->assertCacheMissAndReturnResponse(self::QUERY, ['Store' => $store2->getCode()]);
        $attribute = end($response['body']['attributesList']['items']);
        $this->assertEquals($attribute0->getAttributeCode(), $attribute['code']);

        $this->assertCacheHitAndReturnResponse(self::QUERY, ['Store' => $store2->getCode()]);
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'customer_attribute_0'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'customer_address_attribute_0'
        ),
    ]
    public function testAttributeListChangeOnlyAffectsResponsesWithEntity()
    {
        /** @var AttributeInterface $customerAttribute0 */
        $customerAttribute0 = DataFixtureStorageManager::getStorage()->get('customer_attribute_0');

        /** @var AttributeInterface $customerAttribute0 */
        $customerAddressAttribute0 = DataFixtureStorageManager::getStorage()->get('customer_address_attribute_0');

        $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $response = $this->assertCacheHitAndReturnResponse(self::QUERY, []);

        $attribute = end($response['body']['attributesList']['items']);
        $this->assertEquals($customerAttribute0->getAttributeCode(), $attribute['code']);

        $this->assertCacheMissAndReturnResponse(self::QUERY_ADDRESS, []);
        $this->assertCacheHitAndReturnResponse(self::QUERY_ADDRESS, []);

        $customerAttribute0->setAttributeCode($customerAttribute0->getAttributeCode() . '_modified');
        $this->eavAttributeRepo->save($customerAttribute0);

        $response = $this->assertCacheHitAndReturnResponse(self::QUERY_ADDRESS, []);
        $attribute = end($response['body']['attributesList']['items']);
        $this->assertEquals($customerAddressAttribute0->getAttributeCode(), $attribute['code']);
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
    ]
    public function testAttributesListCacheMissAndHitNewAttribute()
    {
        $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $this->assertCacheHitAndReturnResponse(self::QUERY, []);

        $newAttributeCreate = Bootstrap::getObjectManager()->get(CustomerAttribute::class);
        /** @var AttributeInterface $newAttribute */
        $newAttribute = $newAttributeCreate->apply([
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ]);

        // First query execution should result in a cache miss, while second one should be a cache hit
        $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $this->assertCacheHitAndReturnResponse(self::QUERY, []);

        $this->eavAttributeRepo->delete($newAttribute);

        // Check that the same mentioned above applies if we delete an attribute present in the response
        $this->assertCacheMissAndReturnResponse(self::QUERY, []);
        $this->assertCacheHitAndReturnResponse(self::QUERY, []);
    }
}
