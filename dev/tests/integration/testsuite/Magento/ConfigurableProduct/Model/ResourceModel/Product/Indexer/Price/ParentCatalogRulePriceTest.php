<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Model\Group;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Catalog\Model\Product\Price\GetPriceIndexDataByProductId;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[DbIsolation(false)]
class ParentCatalogRulePriceTest extends TestCase
{
    private const PARENT_SKU = 'configurable-15609-rule';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var int|null
     */
    private $parentRulePriceProductId;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->parentRulePriceProductId = null;
    }

    protected function tearDown(): void
    {
        if ($this->parentRulePriceProductId !== null) {
            $resource = $this->objectManager->get(ResourceConnection::class);
            $resource->getConnection()->delete(
                $resource->getTableName('catalogrule_product_price'),
                ['product_id = ?' => $this->parentRulePriceProductId]
            );
        }
    }

    /**
     * A catalog rule price stored for the parent row itself lowers its final_price and min_price
     * but not its max_price; none of it may reach the children's range.
     */
    #[
        DataFixture(ConfigurableAttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, ['price' => 10], 'child1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['sku' => self::PARENT_SKU, '_options' => ['$attr$'], '_links' => ['$child1$', '$child2$']],
            'configurable'
        ),
    ]
    public function testParentRulePriceDoesNotAffectIndexedMinAndMaxPrice(): void
    {
        $parentId = $this->setParentPrice(30);
        $this->reindexPrices($parentId);
        $this->addParentRulePrice($parentId, 15);
        $this->reindexPrices($parentId);

        $this->assertParentPriceRange($parentId, 10, 20);
    }

    #[
        DataFixture(ConfigurableAttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, ['price' => 10], 'child1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => self::PARENT_SKU,
                '_options' => ['$attr$'],
                '_links' => ['$child1$', '$child2$'],
                'options' => [['is_require' => true, 'price' => 5, 'price_type' => 'fixed']],
            ],
            'configurable'
        ),
    ]
    public function testParentRulePriceKeepsRequiredCustomOptionPrice(): void
    {
        $parentId = $this->setParentPrice(30);
        $this->reindexPrices($parentId);
        $this->addParentRulePrice($parentId, 15);
        $this->reindexPrices($parentId);

        $this->assertParentPriceRange($parentId, 15, 25);
    }

    private function setParentPrice(float $price): int
    {
        $parentId = (int)$this->fixtures->get('configurable')->getId();
        $this->objectManager->get(ProductAction::class)->updateAttributes(
            [$parentId],
            ['price' => $price],
            Store::DEFAULT_STORE_ID
        );

        return $parentId;
    }

    /**
     * Stores a rule price for the parent row, as the catalog rule index does without Magento_CatalogRuleConfigurable
     */
    private function addParentRulePrice(int $parentId, float $rulePrice): void
    {
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $websiteId = $this->getBaseWebsiteId();
        $websiteDate = $connection->fetchOne(
            $connection->select()
                ->from($resource->getTableName('catalog_product_index_website'), ['website_date'])
                ->where('website_id = ?', $websiteId)
        );
        $this->parentRulePriceProductId = $parentId;
        $connection->insert(
            $resource->getTableName('catalogrule_product_price'),
            [
                'rule_date' => $websiteDate,
                'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                'product_id' => $parentId,
                'rule_price' => $rulePrice,
                'website_id' => $websiteId,
                'latest_start_date' => $websiteDate,
                'earliest_end_date' => $websiteDate,
            ]
        );
    }

    private function reindexPrices(int $parentId): void
    {
        $this->objectManager->get(PriceIndexerProcessor::class)
            ->reindexList([...$this->getChildIds(), $parentId], true);
    }

    /**
     * @return int[]
     */
    private function getChildIds(): array
    {
        return [
            (int)$this->fixtures->get('child1')->getId(),
            (int)$this->fixtures->get('child2')->getId(),
        ];
    }

    private function getBaseWebsiteId(): int
    {
        return (int)$this->objectManager->get(WebsiteRepositoryInterface::class)->get('base')->getId();
    }

    private function assertParentPriceRange(int $parentId, float $minPrice, float $maxPrice): void
    {
        $rows = $this->objectManager->get(GetPriceIndexDataByProductId::class)->execute(
            $parentId,
            Group::NOT_LOGGED_IN_ID,
            $this->getBaseWebsiteId()
        );

        $this->assertCount(1, $rows);
        $this->assertEquals($minPrice, $rows[0]['min_price']);
        $this->assertEquals($maxPrice, $rows[0]['max_price']);
    }
}
