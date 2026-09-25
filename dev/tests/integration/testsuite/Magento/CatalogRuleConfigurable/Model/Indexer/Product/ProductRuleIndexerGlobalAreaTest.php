<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Model\Indexer\Product;

use Magento\Catalog\Test\Fixture\AssignCategories as AssignCategoriesFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Partial catalog rule reindex outside adminhtml and crontab must not drop configurable children from the index.
 *
 * Pins down https://github.com/magento/magento2/issues/41081: the configurable-awareness plugins of
 * Magento\CatalogRuleConfigurable apply in every area, so a partial reindex triggered from the global area - an
 * asynchronous indexer consumer, or webapi_rest - must still let the children of a configurable product satisfy a
 * rule condition that only the parent matches. Since Magento\CatalogRule\Model\Indexer\IndexBuilder::doReindexByIds()
 * deletes the catalogrule_product rows of the requested ids before reinserting the ones that still validate, a
 * regression here would silently drop the children's rule prices.
 *
 * The counterpart of this test pinned to the adminhtml area is
 * Magento\CatalogRuleConfigurable\Model\Indexer\Product\ProductRuleIndexerTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRuleIndexerGlobalAreaTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea(Area::AREA_GLOBAL),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(ProductFixture::class, ['price' => 10], 'child1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attribute$'],
                '_links' => ['$child1$', '$child2$'],
            ],
            'configurable'
        ),
        DataFixture(
            AssignCategoriesFixture::class,
            [
                'product' => '$configurable$',
                'categories' => ['$category$'],
            ]
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'simple_action' => 'by_percent',
                'discount_amount' => 50,
                'conditions' => [
                    [
                        'type' => ProductCondition::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '$category.id$',
                    ],
                ],
            ],
            'rule'
        ),
    ]
    public function testPartialReindexKeepsConfigurableChildrenInIndex(): void
    {
        $childIds = [
            (int) $this->fixtures->get('child1')->getId(),
            (int) $this->fixtures->get('child2')->getId(),
        ];
        sort($childIds);
        $ruleId = (int) $this->fixtures->get('rule')->getId();

        $application = Bootstrap::getInstance()->getBootstrap()->getApplication();
        $this->assertSame(
            Area::AREA_GLOBAL,
            $application->getArea(),
            'The reindex has to be executed in the global area for this test to be meaningful.'
        );

        Bootstrap::getObjectManager()->create(ProductRuleIndexer::class)->executeFull();

        $indexedBefore = $this->getIndexedProductIds($ruleId);
        $this->assertSame(
            $childIds,
            $indexedBefore,
            'Precondition failed: a full catalog rule reindex must index the children of the configurable product'
            . ' assigned to the rule category.'
        );

        Bootstrap::getObjectManager()->get(ProductRuleProcessor::class)->reindexList($childIds, true);

        $indexedAfter = $this->getIndexedProductIds($ruleId);
        $lostIds = array_diff($childIds, $indexedAfter);
        $this->assertSame(
            $childIds,
            $indexedAfter,
            sprintf(
                'Partial catalog rule reindex in the "%s" area removed the catalogrule_product rows of the'
                . ' configurable children and did not restore them. Product ids no longer indexed for rule %d: %s.'
                . ' Expected ids: %s. Ids left in the table: %s.',
                Area::AREA_GLOBAL,
                $ruleId,
                $lostIds ? implode(', ', $lostIds) : 'none',
                implode(', ', $childIds),
                $indexedAfter ? implode(', ', $indexedAfter) : 'none'
            )
        );
    }

    /**
     * @param int $ruleId
     * @return int[]
     */
    private function getIndexedProductIds(int $ruleId): array
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->distinct()
            ->from($resource->getTableName('catalogrule_product'), 'product_id')
            ->where('rule_id = ?', $ruleId);

        $productIds = array_map('intval', $connection->fetchCol($select));
        sort($productIds);

        return $productIds;
    }
}
