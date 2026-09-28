<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit test for ScopeOverriddenValue class with 100% coverage
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScopeOverriddenValueTest extends TestCase
{

    use MockCreationTrait;
    /**
     * Test containsValue method with default store ID (early return)
     */
    public function testContainsValueWithDefaultStoreId(): void
    {
        $model = $this->createScopeOverriddenValue();
        $entityMock = $this->createMock(AbstractModel::class);

        $result = $model->containsValue('entity_type', $entityMock, 'attribute_code', Store::DEFAULT_STORE_ID);
        $this->assertFalse($result);
    }

    /**
     * Test our main scenario: empty selects array when all attributes are global
     */
    public function testInitAttributeValuesWithEmptySelectsArray(): void
    {
        $model = $this->createScopeOverriddenValueWithEmptyAttributes();
        $entityMock = $this->createMockEntity();

        // This should not throw exception due to our fix
        $result = $model->containsValue('entity_type', $entityMock, 'attr', 1);
        $this->assertFalse($result);

        // Test getDefaultValues as well
        $defaultValues = $model->getDefaultValues('entity_type', $entityMock);
        $this->assertIsArray($defaultValues);
        $this->assertEmpty($defaultValues);
    }

    /**
     * Test initAttributeValues with no EAV entity type
     */
    public function testInitAttributeValuesWithoutEavEntityType(): void
    {
        $model = $this->createScopeOverriddenValueWithoutEavEntityType();
        $entityMock = $this->createMockEntity();

        $result = $model->containsValue('entity_type', $entityMock, 'attr', 1);
        $this->assertFalse($result);
    }

    /**
     * Test initAttributeValues with static attributes (should be skipped)
     */
    public function testInitAttributeValuesWithStaticAttributes(): void
    {
        $model = $this->createScopeOverriddenValueWithStaticAttributes();
        $entityMock = $this->createMockEntity();

        $result = $model->containsValue('entity_type', $entityMock, 'attr', 1);
        $this->assertFalse($result);
    }

    /**
     * Test full flow with non-static attributes and database query
     */
    public function testInitAttributeValuesWithNonStaticAttributes(): void
    {
        $model = $this->createScopeOverriddenValueWithNonStaticAttributes();
        $entityMock = $this->createMockEntity();

        // Test containsValue - should populate cache and return true for existing attribute
        $result1 = $model->containsValue('entity_type', $entityMock, 'test_attr', 1);
        $this->assertTrue($result1);

        // Test containsValue with cached values - second call should use cache
        $result2 = $model->containsValue('entity_type', $entityMock, 'test_attr', 1);
        $this->assertTrue($result2);

        // Test containsValue for non-existent attribute
        $result3 = $model->containsValue('entity_type', $entityMock, 'nonexistent', 1);
        $this->assertFalse($result3);

        // Test getDefaultValues
        $defaultValues = $model->getDefaultValues('entity_type', $entityMock);
        $this->assertIsArray($defaultValues);
        $this->assertEquals(['test_attr' => 'test_value'], $defaultValues);
    }

    /**
     * Test getDefaultValues when store ID needs to be fetched from entity
     */
    public function testGetDefaultValuesWithEntityStoreId(): void
    {
        $model = $this->createScopeOverriddenValueWithNonStaticAttributes();
        $entityMock = $this->createMockEntity();

        $result = $model->getDefaultValues('entity_type', $entityMock);
        $this->assertIsArray($result);
    }

    /**
     * Test clearAttributesValues when cache exists
     */
    public function testClearAttributesValuesWithExistingCache(): void
    {
        $model = $this->createScopeOverriddenValueWithNonStaticAttributes();
        $entityMock = $this->createMockEntity();

        // First populate cache
        $model->containsValue('entity_type', $entityMock, 'test_attr', 1);

        // Now clear cache - should work without exception
        $entity = new DataObject(['entity_id' => 1]);
        $model->clearAttributesValues('entity_type', $entity);
        $this->assertTrue(true);
    }

    /**
     * Test clearAttributesValues when no cache exists
     */
    public function testClearAttributesValuesWithNoCache(): void
    {
        $model = $this->createScopeOverriddenValue();
        $entity = new DataObject(['entity_id' => 1]);

        // Should not throw exception when no cache exists
        $model->clearAttributesValues('entity_type', $entity);
        $this->assertTrue(true);
    }

    /**
     * Test with non-default store ID to cover store ID logic
     */
    public function testWithNonDefaultStoreId(): void
    {
        $model = $this->createScopeOverriddenValueWithNonStaticAttributes();
        $entityMock = $this->createMockEntity();

        // Test with store ID 2
        $result = $model->containsValue('entity_type', $entityMock, 'test_attr', 2);
        $this->assertTrue($result);
    }

    /**
     * Create basic ScopeOverriddenValue instance
     */
    private function createScopeOverriddenValue(): ScopeOverriddenValue
    {
        return new ScopeOverriddenValue(
            $this->createMock(AttributeRepositoryInterface::class),
            $this->createMock(MetadataPool::class),
            $this->createMock(SearchCriteriaBuilder::class),
            $this->createMock(FilterBuilder::class),
            $this->createMock(ResourceConnection::class)
        );
    }

    /**
     * Create ScopeOverriddenValue with empty attributes (global scope scenario)
     */
    private function createScopeOverriddenValueWithEmptyAttributes(): ScopeOverriddenValue
    {
        $attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $filterBuilderMock = $this->createMock(FilterBuilder::class);
        $resourceConnectionMock = $this->createMock(ResourceConnection::class);

        // Setup metadata
        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->method('getEavEntityType')->willReturn('catalog_product');
        $metadataMock->method('getLinkField')->willReturn('entity_id');
        $metadataPoolMock->method('getMetadata')->willReturn($metadataMock);

        // Setup empty attributes
        $searchResultsMock = $this->createMock(AttributeSearchResultsInterface::class);
        $searchResultsMock->method('getItems')->willReturn([]);

        // Setup search criteria
        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);

        $filterBuilderMock->method('setField')->willReturnSelf();
        $filterBuilderMock->method('setConditionType')->willReturnSelf();
        $filterBuilderMock->method('setValue')->willReturnSelf();
        $filterBuilderMock->method('create')->willReturn($filterMock);

        $searchCriteriaBuilderMock->method('addFilters')->willReturnSelf();
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);

        $attributeRepositoryMock->method('getList')->willReturn($searchResultsMock);

        return new ScopeOverriddenValue(
            $attributeRepositoryMock,
            $metadataPoolMock,
            $searchCriteriaBuilderMock,
            $filterBuilderMock,
            $resourceConnectionMock
        );
    }

    /**
     * Create ScopeOverriddenValue without EAV entity type
     */
    private function createScopeOverriddenValueWithoutEavEntityType(): ScopeOverriddenValue
    {
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadataMock = $this->createMock(EntityMetadataInterface::class);

        $metadataMock->method('getEavEntityType')->willReturn(null);
        $metadataPoolMock->method('getMetadata')->willReturn($metadataMock);

        return new ScopeOverriddenValue(
            $this->createMock(AttributeRepositoryInterface::class),
            $metadataPoolMock,
            $this->createMock(SearchCriteriaBuilder::class),
            $this->createMock(FilterBuilder::class),
            $this->createMock(ResourceConnection::class)
        );
    }

    /**
     * Create ScopeOverriddenValue with static attributes
     */
    private function createScopeOverriddenValueWithStaticAttributes(): ScopeOverriddenValue
    {
        $attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $filterBuilderMock = $this->createMock(FilterBuilder::class);
        $resourceConnectionMock = $this->createMock(ResourceConnection::class);

        // Setup metadata
        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->method('getEavEntityType')->willReturn('catalog_product');
        $metadataMock->method('getLinkField')->willReturn('entity_id');
        $metadataPoolMock->method('getMetadata')->willReturn($metadataMock);

        // Setup static attribute
        $staticAttributeMock = $this->createMock(AbstractAttribute::class);
        $staticAttributeMock->method('isStatic')->willReturn(true);

        $searchResultsMock = $this->createMock(AttributeSearchResultsInterface::class);
        $searchResultsMock->method('getItems')->willReturn([$staticAttributeMock]);

        // Setup search criteria
        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);

        $filterBuilderMock->method('setField')->willReturnSelf();
        $filterBuilderMock->method('setConditionType')->willReturnSelf();
        $filterBuilderMock->method('setValue')->willReturnSelf();
        $filterBuilderMock->method('create')->willReturn($filterMock);

        $searchCriteriaBuilderMock->method('addFilters')->willReturnSelf();
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);

        $attributeRepositoryMock->method('getList')->willReturn($searchResultsMock);

        return new ScopeOverriddenValue(
            $attributeRepositoryMock,
            $metadataPoolMock,
            $searchCriteriaBuilderMock,
            $filterBuilderMock,
            $resourceConnectionMock
        );
    }

    /**
     * Create ScopeOverriddenValue with non-static attributes and full database simulation
     */
    private function createScopeOverriddenValueWithNonStaticAttributes(): ScopeOverriddenValue
    {
        $attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $filterBuilderMock = $this->createMock(FilterBuilder::class);
        $resourceConnectionMock = $this->createMock(ResourceConnection::class);

        // Setup database connection and select
        $connectionMock = $this->createMock(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $connectionMock->method('select')->willReturn($selectMock);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('join')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();

        // Mock database results
        $connectionMock->method('fetchAll')->willReturn([
            ['attribute_code' => 'test_attr', 'value' => 'test_value', 'store_id' => '0'],
            ['attribute_code' => 'test_attr', 'value' => 'test_value', 'store_id' => '1'],
            ['attribute_code' => 'test_attr', 'value' => 'test_value', 'store_id' => '2']
        ]);

        // Setup metadata
        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->method('getEavEntityType')->willReturn('catalog_product');
        $metadataMock->method('getLinkField')->willReturn('entity_id');
        $metadataMock->method('getEntityConnection')->willReturn($connectionMock);
        $metadataPoolMock->method('getMetadata')->willReturn($metadataMock);

        // Setup non-static attribute
        $attributeMock = $this->createMock(AbstractAttribute::class);
        $attributeMock->method('isStatic')->willReturn(false);

        $backendMock = $this->createMock(AbstractBackend::class);
        $backendMock->method('getTable')->willReturn('catalog_product_entity_varchar');
        $attributeMock->method('getBackend')->willReturn($backendMock);
        $attributeMock->method('getAttributeId')->willReturn(1);

        $searchResultsMock = $this->createMock(AttributeSearchResultsInterface::class);
        $searchResultsMock->method('getItems')->willReturn([$attributeMock]);

        // Setup search criteria
        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);

        $filterBuilderMock->method('setField')->willReturnSelf();
        $filterBuilderMock->method('setConditionType')->willReturnSelf();
        $filterBuilderMock->method('setValue')->willReturnSelf();
        $filterBuilderMock->method('create')->willReturn($filterMock);

        $searchCriteriaBuilderMock->method('addFilters')->willReturnSelf();
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);

        $attributeRepositoryMock->method('getList')->willReturn($searchResultsMock);

        $resourceConnectionMock->method('getTableName')->willReturn('eav_attribute');

        return new ScopeOverriddenValue(
            $attributeRepositoryMock,
            $metadataPoolMock,
            $searchCriteriaBuilderMock,
            $filterBuilderMock,
            $resourceConnectionMock
        );
    }

    /**
     * Create mock entity
     */
    private function createMockEntity(): MockObject
    {
        $entityMock = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['getData', 'getStoreId']
        );
        $entityMock->method('getData')->willReturn(1);
        $entityMock->method('getStoreId')->willReturn(0);
        return $entityMock;
    }
}
