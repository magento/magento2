<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Model\Indexer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Rule\Condition\Product as RuleConditionProduct;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Test\Fixture\CustomerGroup as CustomerGroupFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies catalog rules with the same priority but different customer groups apply to configurable children.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml')]
#[DbIsolation(false)]
class ReindexRuleProductSamePriorityCustomerGroupsTest extends TestCase
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
        $this->indexBuilder = $objectManager->get(IndexBuilder::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, [], 'child1'),
        DataFixture(ProductFixture::class, [], 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$child1$', '$child2$']],
            'configurable'
        ),
        DataFixture(CustomerGroupFixture::class, [GroupInterface::CODE => 'custom_group'], 'customGroup'),
        DataFixture(CatalogRuleFixture::class, [
            'name' => 'Percent rule for configurable product',
            'website_ids' => [1],
            'customer_group_ids' => [Group::NOT_LOGGED_IN_ID],
            'discount_amount' => 50,
            'sort_order' => 0,
            'conditions' => [
                [
                    'type' => RuleConditionProduct::class,
                    'attribute' => 'sku',
                    'operator' => '==',
                    'value' => '$configurable.sku$',
                ],
            ],
        ]),
        DataFixture(CatalogRuleFixture::class, [
            'name' => 'Same priority rule for NOT LOGGED IN customer group',
            'website_ids' => [1],
            'customer_group_ids' => [Group::NOT_LOGGED_IN_ID],
            'discount_amount' => 10,
            'sort_order' => 0,
            'conditions' => [
                [
                    'type' => RuleConditionProduct::class,
                    'attribute' => 'sku',
                    'operator' => '()',
                    'value' => '$child1.sku$,$child2.sku$',
                ],
            ],
        ]),
        DataFixture(CatalogRuleFixture::class, [
            'name' => 'Same priority rule for custom customer group',
            'website_ids' => [1],
            'customer_group_ids' => ['$customGroup.id$'],
            'discount_amount' => 20,
            'sort_order' => 0,
            'conditions' => [
                [
                    'type' => RuleConditionProduct::class,
                    'attribute' => 'sku',
                    'operator' => '()',
                    'value' => '$child1.sku$,$child2.sku$',
                ],
            ],
        ]),
    ]
    public function testBothSamePriorityRulesApplyToConfigurableChildrenForTheirOwnCustomerGroup(): void
    {
        $this->indexBuilder->reindexFull();

        $websiteId = (int)$this->websiteRepository->get('base')->getId();
        $customGroupId = (int)$this->fixtures->get('customGroup')->getId();

        foreach (['child1', 'child2'] as $alias) {
            $productId = (int)$this->fixtures->get($alias)->getId();

            $customerGroupIds = $this->getIndexedCustomerGroupIds($productId, $websiteId);

            $this->assertContains(
                Group::NOT_LOGGED_IN_ID,
                $customerGroupIds,
                "Product {$alias} is missing the catalog rule indexed for the NOT LOGGED IN customer group"
            );
            $this->assertContains(
                $customGroupId,
                $customerGroupIds,
                "Product {$alias} is missing the catalog rule indexed for the custom customer group"
            );
        }
    }

    /**
     * Get customer group ids for which a catalogrule_product row exists for the given product and website.
     *
     * @param int $productId
     * @param int $websiteId
     * @return int[]
     */
    private function getIndexedCustomerGroupIds(int $productId, int $websiteId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalogrule_product'), ['customer_group_id'])
            ->where('product_id = ?', $productId)
            ->where('website_id = ?', $websiteId);

        return array_map('intval', $connection->fetchCol($select));
    }
}
