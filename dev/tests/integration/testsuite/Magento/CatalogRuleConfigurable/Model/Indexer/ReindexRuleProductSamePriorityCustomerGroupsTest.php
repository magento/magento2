<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Model\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Customer\Model\Group;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies catalog rules with the same priority but different customer groups apply to configurable children.
 */
#[AppArea('adminhtml')]
#[DbIsolation(false)]
class ReindexRuleProductSamePriorityCustomerGroupsTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
        $this->indexBuilder = $objectManager->get(IndexBuilder::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
    }

    #[DataFixture('Magento/CatalogRuleConfigurable/_files/configurable_same_priority_rules_per_customer_group.php')]
    public function testBothSamePriorityRulesApplyToConfigurableChildrenForTheirOwnCustomerGroup(): void
    {
        $this->indexBuilder->reindexFull();

        $websiteId = (int)$this->websiteRepository->get('base')->getId();
        /** @var Group $customGroup */
        $customGroup = Bootstrap::getObjectManager()->create(Group::class)
            ->load('custom_group', 'customer_group_code');
        $customGroupId = (int)$customGroup->getId();

        foreach (['simple_10', 'simple_20'] as $sku) {
            $productId = (int)$this->productRepository->get($sku)->getId();

            $customerGroupIds = $this->getIndexedCustomerGroupIds($productId, $websiteId);

            $this->assertContains(
                Group::NOT_LOGGED_IN_ID,
                $customerGroupIds,
                "Product {$sku} is missing the catalog rule indexed for the NOT LOGGED IN customer group"
            );
            $this->assertContains(
                $customGroupId,
                $customerGroupIds,
                "Product {$sku} is missing the catalog rule indexed for the custom customer group"
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
