<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * A composite product created without stock data must still be able to follow its children back into stock.
 *
 * The legacy stock item row for such a product is created implicitly, with is_in_stock = 0 and
 * stock_status_changed_auto = 0. ChangeParentStockStatus lets a parent go out of stock unconditionally but
 * only lets it come back when stock_status_changed_auto is set, so the parent is latched out of stock
 * permanently. Reindexing does not help: the flag is stored, not derived.
 */
class CompositeParentStockStatusTest extends TestCase
{
    private const PARENT_SKU = 'composite-latch-parent';

    private const GROUPED_SKU = 'composite-latch-grouped';

    private const BARE_SKU = 'composite-latch-bare-configurable';

    private const BARE_BUNDLE_SKU = 'composite-latch-bare-bundle';

    /**
     * The children arrive before their stock does, which is the ordinary ERP import order: the structure
     * feed creates the catalogue, a later stock feed fills in quantities. The parent is therefore created
     * at a moment when none of its children are salable.
     */
    private const CHILD_WITHOUT_STOCK = [
        'extension_attributes' => [
            'stock_item' => [
                'use_config_manage_stock' => true,
                'qty' => 0,
                'is_qty_decimal' => false,
                'is_in_stock' => false,
            ],
        ],
    ];

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->resource = $objectManager->get(ResourceConnection::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Read the raw legacy stock row rather than a loaded model.
     *
     * A repository round trip is served from the request-level stock registry cache, which reports the value
     * the process last wrote rather than the value that was persisted.
     *
     * @param string $sku
     * @return array
     */
    private function loadStockRow(string $sku): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['si' => $this->resource->getTableName('cataloginventory_stock_item')], [
                'is_in_stock',
                'stock_status_changed_auto',
            ])
            ->joinInner(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                'e.entity_id = si.product_id',
                []
            )
            ->where('e.sku = ?', $sku);

        $row = $connection->fetchRow($select);
        self::assertNotEmpty($row, sprintf('No legacy stock row exists for SKU "%s".', $sku));

        return array_map('intval', $row);
    }

    /**
     * The implicitly created stock row of a composite product records no merchant decision, so it must start
     * under automatic control.
     *
     * @return void
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, self::CHILD_WITHOUT_STOCK, 'child1'),
        DataFixture(ProductFixture::class, self::CHILD_WITHOUT_STOCK, 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => self::PARENT_SKU,
                '_options' => ['$attr$'],
                '_links' => ['$child1$', '$child2$'],
                'extension_attributes' => ['stock_item' => null],
            ],
            'parent'
        ),
    ]
    public function testTheAutomaticMarkerSurvivesALaterSaveOfTheParent(): void
    {
        $born = $this->loadStockRow(self::PARENT_SKU);

        // Verify the premise. Core takes the parent out of stock automatically because none of its children
        // are salable yet, and marks it as automatically maintained, so at this point it can still recover.
        self::assertSame(
            [0, 1],
            [$born['is_in_stock'], $born['stock_status_changed_auto']],
            'PREMISE FAILED: the parent should be born out of stock but under automatic control. '
            . 'Actual: ' . json_encode($born)
        );

        // A later structure feed touches the parent again - a rename, a category change, anything.
        $this->resaveParent();

        $after = $this->loadStockRow(self::PARENT_SKU);

        self::assertSame(
            1,
            $after['stock_status_changed_auto'],
            sprintf(
                'An ordinary save of the parent must not be mistaken for a merchant taking it off sale. '
                . 'The parent was (is_in_stock=%d, auto=%d) and is now (is_in_stock=%d, auto=%d): the '
                . 'automatic marker was cleared, which latches the parent out of stock permanently.',
                $born['is_in_stock'],
                $born['stock_status_changed_auto'],
                $after['is_in_stock'],
                $after['stock_status_changed_auto']
            )
        );
    }

    /**
     * The headline regression: a parent imported without stock data must follow its children back into stock.
     *
     * @return void
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, self::CHILD_WITHOUT_STOCK, 'child1'),
        DataFixture(ProductFixture::class, self::CHILD_WITHOUT_STOCK, 'child2'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => self::PARENT_SKU,
                '_options' => ['$attr$'],
                '_links' => ['$child1$', '$child2$'],
                'extension_attributes' => ['stock_item' => null],
            ],
            'parent'
        ),
    ]
    public function testTheParentFollowsItsChildrenBackIntoStock(): void
    {
        $born = $this->loadStockRow(self::PARENT_SKU);

        // A later structure feed touches the parent again, before any stock has arrived.
        $this->resaveParent();
        $before = $this->loadStockRow(self::PARENT_SKU);

        // The stock feed finally arrives and makes a child salable, which triggers the parent recompute.
        $this->resaveChild('child1');

        $after = $this->loadStockRow(self::PARENT_SKU);

        self::assertSame(
            1,
            $after['is_in_stock'],
            sprintf(
                'The parent must follow its in-stock children. Born (is_in_stock=%d, auto=%d); '
                . 'after a later save of the parent (is_in_stock=%d, auto=%d); '
                . 'after a child was stocked (is_in_stock=%d, auto=%d).',
                $born['is_in_stock'],
                $born['stock_status_changed_auto'],
                $before['is_in_stock'],
                $before['stock_status_changed_auto'],
                $after['is_in_stock'],
                $after['stock_status_changed_auto']
            )
        );
    }

    /**
     * Re-save a child product through the repository, mirroring a second stock feed.
     *
     * @param string $fixtureName
     * @return void
     */
    private function resaveParent(): void
    {
        $this->startNewRequest();
        $repository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $repository->get(self::PARENT_SKU, true, null, true);
        $product->setName($product->getName() . ' (updated)');
        $repository->save($product);
    }

    /**
     * Re-save a child product through the repository, mirroring a second stock feed.
     *
     * @param string $fixtureName
     * @return void
     */
    private function resaveChild(string $fixtureName): void
    {
        $this->startNewRequest();
        /** @var ProductInterface $child */
        $child = $this->fixtures->get($fixtureName);
        $repository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $repository->get($child->getSku(), true, null, true);
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(100);
        $stockItem->setIsInStock(true);
        $repository->save($product);
    }

    /**
     * Drop the request-level stock registry so the next save behaves like a fresh HTTP request.
     *
     * Every save in this test runs inside one PHP process, so an in-memory stock item - and in particular
     * the StockStatusChangedAutomaticallyFlag that ChangeParentStockStatus sets on it - would otherwise
     * survive into the next save. A real import sends each feed in its own request, where that marker is
     * absent. Without this the test silently measures a state no production request ever sees.
     *
     * @return void
     */
    private function startNewRequest(): void
    {
        Bootstrap::getObjectManager()->get(StockRegistryStorage::class)->clean();
    }

    /**
     * The grouped variant of the latch, following the sequence an ERP feed actually produces: the parent is
     * created before it has any children, the children are attached by a later feed, and their stock arrives
     * after that.
     *
     * @return void
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(ProductFixture::class, self::CHILD_WITHOUT_STOCK, 'child1'),
        DataFixture(ProductFixture::class, self::CHILD_WITHOUT_STOCK, 'child2'),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => self::GROUPED_SKU,
                'product_links' => [],
                'extension_attributes' => ['stock_item' => null],
            ],
            'grouped'
        ),
    ]
    public function testAGroupedParentSurvivesHavingItsChildrenAttachedLater(): void
    {
        $born = $this->loadStockRow(self::GROUPED_SKU);

        $this->attachGroupedChildren();
        $attached = $this->loadStockRow(self::GROUPED_SKU);

        $this->resaveChild('child1');
        $after = $this->loadStockRow(self::GROUPED_SKU);

        self::assertSame(
            1,
            $after['is_in_stock'],
            sprintf(
                'The grouped parent must follow its in-stock children. '
                . 'Born (is_in_stock=%d, auto=%d); after children were attached (is_in_stock=%d, auto=%d); '
                . 'after a child was stocked (is_in_stock=%d, auto=%d).',
                $born['is_in_stock'],
                $born['stock_status_changed_auto'],
                $attached['is_in_stock'],
                $attached['stock_status_changed_auto'],
                $after['is_in_stock'],
                $after['stock_status_changed_auto']
            )
        );
    }

    /**
     * Attach the two simple children to the grouped parent in a save of its own.
     *
     * @return void
     */
    private function attachGroupedChildren(): void
    {
        $this->startNewRequest();
        $objectManager = Bootstrap::getObjectManager();
        $repository = $objectManager->get(ProductRepositoryInterface::class);
        $linkFactory = $objectManager->get(ProductLinkInterfaceFactory::class);

        $product = $repository->get(self::GROUPED_SKU, true, null, true);
        $links = [];
        $position = 1;
        foreach (['child1', 'child2'] as $fixtureName) {
            $child = $this->fixtures->get($fixtureName);
            $link = $linkFactory->create();
            $link->setSku(self::GROUPED_SKU)
                ->setLinkType('associated')
                ->setLinkedProductSku($child->getSku())
                ->setLinkedProductType('simple')
                ->setPosition($position++);
            $links[] = $link;
        }
        $product->setProductLinks($links);
        $repository->save($product);
    }

    /**
     * A configurable created before its variations exist - the ordinary structure-feed order - must still be
     * under automatic control, or it can never be brought back into stock.
     *
     * @return void
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => self::BARE_SKU,
                'extension_attributes' => ['stock_item' => null],
            ],
            'bare'
        ),
    ]
    public function testAConfigurableCreatedBeforeItsVariationsIsBornUnderAutomaticControl(): void
    {
        $row = $this->loadStockRow(self::BARE_SKU);

        self::assertSame(
            1,
            $row['stock_status_changed_auto'],
            sprintf(
                'A composite product created without stock data records no merchant decision, so it must '
                . 'start under automatic control. Actual row: %s. With the flag clear the parent is latched '
                . 'out of stock permanently: ChangeParentStockStatus will never move it back.',
                json_encode($row)
            )
        );
    }

    /**
     * The bundle variant of the same defect. Shipment type is Ship Separately, because a Ship Together
     * bundle cannot carry multi-source children and would constrain later multi-source coverage.
     *
     * @return void
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => self::BARE_BUNDLE_SKU,
                'custom_attributes' => [
                    'price_view' => '0',
                    'sku_type' => '0',
                    'price_type' => '0',
                    'weight_type' => '0',
                    // Ship Separately: a Ship Together bundle cannot carry multi-source children.
                    'shipment_type' => '1',
                ],
                'extension_attributes' => ['stock_item' => null],
            ],
            'bareBundle'
        ),
    ]
    public function testABundleCreatedBeforeItsSelectionsIsBornUnderAutomaticControl(): void
    {
        $row = $this->loadStockRow(self::BARE_BUNDLE_SKU);

        self::assertSame(
            1,
            $row['stock_status_changed_auto'],
            sprintf(
                'A bundle created without stock data records no merchant decision, so it must start under '
                . 'automatic control. Actual row: %s.',
                json_encode($row)
            )
        );
    }
}
