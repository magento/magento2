<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the admin product grid in custom options popup
 *
 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid
 * @magentoAppArea adminhtml
 */
class GridTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var Grid
     */
    private Grid $block;

    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        Bootstrap::getInstance()->loadArea(FrontNameResolver::AREA_CODE);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Grid::class);
        $this->block->toHtml();
    }

    /**
     * Test that getRowUrl returns null to disable JS click events
     *
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::getRowUrl
     */
    public function testGetRowUrlReturnsNull(): void
    {
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->setId(1);
        $product->setName('Test Product');

        $result = $this->block->getRowUrl($product);

        $this->assertNull($result, 'getRowUrl should return null to disable JS click events');
    }

    /**
     * Test that getRowUrl returns null for DataObject
     *
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::getRowUrl
     */
    public function testGetRowUrlReturnsNullForDataObject(): void
    {
        $dataObject = $this->objectManager->create(\Magento\Framework\DataObject::class);
        $dataObject->setData(['id' => 1, 'name' => 'Test']);

        $result = $this->block->getRowUrl($dataObject);

        $this->assertNull($result);
    }

    /**
     * Test that specific columns are removed from the grid
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::_prepareColumns
     */
    public function testPrepareColumnsRemovesSpecificColumns(): void
    {
        // Verify that action, status, and visibility columns are removed
        // getColumn returns false when column doesn't exist
        $this->assertFalse(
            $this->block->getColumn('action'),
            'The action column should be removed'
        );
        $this->assertFalse(
            $this->block->getColumn('status'),
            'The status column should be removed'
        );
        $this->assertFalse(
            $this->block->getColumn('visibility'),
            'The visibility column should be removed'
        );

        // Verify that other essential columns still exist
        $this->assertNotFalse(
            $this->block->getColumn('entity_id'),
            'The entity_id column should exist'
        );
        $this->assertNotFalse(
            $this->block->getColumn('name'),
            'The name column should exist'
        );
        $this->assertNotFalse(
            $this->block->getColumn('sku'),
            'The sku column should exist'
        );
    }

    /**
     * Test that massaction is configured with import action
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::_prepareMassaction
     */
    public function testPrepareMassactionAddsImportAction(): void
    {
        $massactionBlock = $this->block->getMassactionBlock();

        $this->assertNotNull($massactionBlock, 'Massaction block should exist');
        $this->assertEquals('entity_id', $this->block->getMassactionIdField());

        // Check that import action is available
        $importItem = $massactionBlock->getItem('import');
        $this->assertNotNull($importItem, 'Import action should be available in massaction');
        $this->assertEquals('Import', (string) $importItem->getLabel());
    }

    /**
     * Test that collection joins with options table to filter products
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers ::_prepareCollection
     */
    public function testPrepareCollectionJoinsWithOptionsTable(): void
    {
        $collection = $this->block->getCollection();

        $this->assertNotNull($collection, 'Collection should be set');
        $this->assertInstanceOf(
            ProductCollection::class,
            $collection,
            'Collection should be an instance of Product Collection'
        );

        // Verify the collection has the proper join - check that SELECT contains catalog_product_option
        $selectString = $collection->getSelect()->__toString();
        $this->assertStringContainsString(
            'catalog_product_option',
            $selectString,
            'Collection should join with catalog_product_option table'
        );
    }

    /**
     * Test that current product filter is applied to collection when parameter is set
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::_prepareCollection
     */
    public function testPrepareCollectionAppliesCurrentProductFilter(): void
    {
        $testProductId = 999;
        $request = $this->objectManager->get(RequestInterface::class);

        try {
            // Simulate request with current_product_id parameter
            $request->setParam('current_product_id', $testProductId);

            // Create a new block instance with the updated request
            $blockWithRequest = $this->layout->createBlock(Grid::class);
            $blockWithRequest->toHtml();

            $collection = $blockWithRequest->getCollection();

            // Verify the WHERE clause excludes the current product
            $selectString = $collection->getSelect()->__toString();
            $this->assertMatchesRegularExpression(
                '/e\.entity_id\s*!=\s*[\'"]?999[\'"]?/i',
                $selectString,
                'Collection should have WHERE clause excluding current product ID'
            );
        } finally {
            // Clean up request parameter to avoid affecting other tests
            $request->setParam('current_product_id', null);
        }
    }

    /**
     * Test that getGridUrl returns the correct URL for AJAX updates
     *
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::getGridUrl
     */
    public function testGetGridUrlReturnsCorrectUrl(): void
    {
        $gridUrl = $this->block->getGridUrl();

        $this->assertNotEmpty($gridUrl, 'Grid URL should not be empty');
        $this->assertStringContainsString(
            'optionsimportgrid',
            $gridUrl,
            'Grid URL should contain "optionsimportgrid" action'
        );
    }

    /**
     * Test that grid has correct massaction form field name
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::_prepareMassaction
     */
    public function testMassactionFormFieldName(): void
    {
        $massactionBlock = $this->block->getMassactionBlock();

        $this->assertEquals(
            'product',
            $massactionBlock->getFormFieldName(),
            'Massaction form field name should be "product"'
        );
    }

    /**
     * Test grid block is properly initialized after rendering
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::_prepareCollection
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::_prepareMassaction
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Popup\Grid::getGridUrl
     */
    public function testGridBlockIsProperlyInitialized(): void
    {
        // Verify block is properly initialized (toHtml already called in setUp)
        $this->assertNotNull($this->block->getCollection(), 'Collection should be initialized');
        $this->assertNotNull($this->block->getMassactionBlock(), 'Massaction block should be initialized');
        $this->assertNotEmpty($this->block->getGridUrl(), 'Grid URL should be set');
    }

    /**
     * Test that grid collection uses DISTINCT to avoid duplicate products
     *
     * @magentoDbIsolation enabled
     * @return void
     * @covers ::_prepareCollection
     */
    public function testCollectionIsDistinct(): void
    {
        $collection = $this->block->getCollection();

        // Verify the collection query uses DISTINCT
        $selectString = $collection->getSelect()->__toString();
        $this->assertStringContainsString(
            'DISTINCT',
            $selectString,
            'Collection should use DISTINCT to avoid duplicate products'
        );
    }
}
