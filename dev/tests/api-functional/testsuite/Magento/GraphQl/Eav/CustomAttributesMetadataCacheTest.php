<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Eav;

use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\TestFramework\Helper\Bootstrap;

class CustomAttributesMetadataCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

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
        /** @var \Magento\Eav\Model\AttributeRepository $eavAttributeRepo */
        $eavAttributeRepo = $this->objectManager->get(\Magento\Eav\Model\AttributeRepository::class);
        /** @var \Magento\Store\Model\StoreRepository $storeRepo */
        $storeRepo = $this->objectManager->get(\Magento\Store\Model\StoreRepository::class);

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
        /** @var \Magento\Eav\Model\AttributeRepository $eavAttributeRepo */
        $eavAttributeRepo = $this->objectManager->get(\Magento\Eav\Model\AttributeRepository::class);
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
}
