<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ProductAttribute;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Customer\Test\Fixture\CustomerAttribute as CustomerAttributeFixture;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryGetListTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var Config
     */
    private Config $eavConfig;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->eavConfig = $this->objectManager->get(Config::class);
    }

    #[
        DataFixture(
            AttributeFixture::class,
            ['is_searchable' => true, 'is_comparable' => true, 'used_in_product_listing' => true],
            'first'
        ),
        DataFixture(
            AttributeFixture::class,
            ['is_searchable' => true, 'is_comparable' => true, 'used_in_product_listing' => true],
            'second'
        ),
    ]
    public function testGetListBuildsProductAttributesFromTheListQuery(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $codes = [
            $fixtures->get('first')->getAttributeCode(),
            $fixtures->get('second')->getAttributeCode(),
        ];

        [$items, $queries] = $this->getListWithQueries(ProductAttributeInterface::ENTITY_TYPE_CODE, $codes);

        $this->assertNotEmpty($this->filterQueries($queries, 'additional_table'));
        $this->assertSame([], $this->filterQueries($queries, 'entity_type_id = :entity_type_id'));
        $this->assertSame([], $this->filterQueries($queries, 'attribute_id = :attribute_id'));
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertContains($item->getAttributeCode(), $codes);
            $this->assertSame(
                $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $item->getAttributeCode()),
                $item
            );
            $this->assertEquals(1, $item->getIsComparable());
            $this->assertEquals(1, $item->getIsSearchable());
            $this->assertEquals(1, $item->getUsedInProductListing());
            $this->assertSame(
                $this->eavConfig->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE),
                $item->getData('entity_type')
            );
            $this->assertSameAsLoadedByCode(
                ProductAttribute::class,
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $item
            );
        }
    }

    #[
        DataFixture(
            CustomerAttributeFixture::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => 'list_test_customer_attribute',
                'frontend_input' => 'text',
                'backend_type' => 'varchar',
            ],
            'customer_attribute'
        ),
    ]
    public function testGetListKeepsLoadingWebsiteScopedAttributesByCode(): void
    {
        $code = DataFixtureStorageManager::getStorage()->get('customer_attribute')->getAttributeCode();

        [$items, $queries] = $this->getListWithQueries(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, [$code]);

        $this->assertNotEmpty($this->filterQueries($queries, 'entity_type_id = :entity_type_id'));
        $this->assertCount(1, $items);
        $item = reset($items);
        $this->assertSame($code, $item->getAttributeCode());
        $this->assertSameAsLoadedByCode(
            CustomerAttribute::class,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $item
        );
    }

    /**
     * @param string $entityTypeCode
     * @param string[] $codes
     * @return array{0: AbstractAttribute[], 1: string[]}
     */
    private function getListWithQueries(string $entityTypeCode, array $codes): array
    {
        $this->eavConfig->clear();
        $searchCriteria = $this->objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter('attribute_code', $codes, 'in')
            ->create();
        $repository = $this->objectManager->get(AttributeRepositoryInterface::class);

        $profiler = $this->objectManager->get(ResourceConnection::class)->getConnection()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        try {
            $items = $repository->getList($entityTypeCode, $searchCriteria)->getItems();
            $queries = array_map(
                static fn ($profile) => $profile->getQuery(),
                $profiler->getQueryProfiles(null, true) ?: []
            );
        } finally {
            $profiler->setEnabled(false);
            $profiler->clear();
        }

        return [$items, $queries];
    }

    /**
     * @param string[] $queries
     * @param string $needle
     * @return string[]
     */
    private function filterQueries(array $queries, string $needle): array
    {
        return array_values(array_filter($queries, static fn (string $query) => str_contains($query, $needle)));
    }

    private function assertSameAsLoadedByCode(string $model, string $entityTypeCode, AbstractAttribute $item): void
    {
        $loaded = $this->objectManager->create($model)->loadByCode($entityTypeCode, $item->getAttributeCode());
        foreach ($loaded->getData() as $key => $value) {
            $this->assertEquals($value, $item->getData($key), 'Attribute field "' . $key . '" differs');
        }
    }
}
