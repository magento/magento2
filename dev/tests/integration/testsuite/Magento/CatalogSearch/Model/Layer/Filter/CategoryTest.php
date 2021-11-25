<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Layer\Filter;

/**
 * Test class for \Magento\CatalogSearch\Model\Layer\Filter\Category.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    const CURRENT_CATEGORY_FILTER = 'current_category_filter';

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Category
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    protected function setUp(): void
    {
        $this->_category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $this->_category->load(5);
        $layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Catalog\Model\Layer\Category::class,
                ['data' => ['current_category' => $this->_category]]
            );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\CatalogSearch\Model\Layer\Filter\Category::class, ['layer' => $layer]);
        $this->_model->setRequestVar('cat');
    }

    protected function tearDown(): void
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister(self::CURRENT_CATEGORY_FILTER);
    }

    public function testGetResetValue()
    {
        $this->assertNull($this->_model->getResetValue());
    }

    public function testApplyNothing()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model->apply(
            $objectManager->get(\Magento\TestFramework\Request::class),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\View\LayoutInterface::class
            )->createBlock(
                \Magento\Framework\View\Element\Text::class
            )
        );
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get(\Magento\Framework\Registry::class)->registry(self::CURRENT_CATEGORY_FILTER);
        $this->assertInstanceOf(\Magento\Catalog\Model\Category::class, $category);
        $this->assertEquals($this->_category->getId(), $category->getId());
    }

    public function testApply()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('cat', 3);
        $this->_model->apply($request);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get(\Magento\Framework\Registry::class)->registry(self::CURRENT_CATEGORY_FILTER);
        $this->assertInstanceOf(\Magento\Catalog\Model\Category::class, $category);
        $this->assertEquals(3, $category->getId());

        return $this->_model;
    }

    /**
     * @depends testApply
     */
    public function testGetResetValueApplied(\Magento\CatalogSearch\Model\Layer\Filter\Category $modelApplied)
    {
        $this->assertEquals(2, $modelApplied->getResetValue());
    }

    public function testGetName()
    {
        $this->assertEquals('Category', $this->_model->getName());
    }

    /**
     * @magentoDbIsolation disabled
     */
    public function testGetItems()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('cat', 3);
        $this->_model->apply($request);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get(\Magento\Framework\Registry::class)->registry(self::CURRENT_CATEGORY_FILTER);
        $this->assertInstanceOf(\Magento\Catalog\Model\Category::class, $category);
        $this->assertEquals(3, $category->getId());

        $items = $this->_model->getItems();

        $this->assertIsArray($items);
        $this->assertCount(2, $items);

        /** @var $item \Magento\Catalog\Model\Layer\Filter\Item */
        $item = $items[0];
        $this->assertInstanceOf(\Magento\Catalog\Model\Layer\Filter\Item::class, $item);
        $this->assertSame($this->_model, $item->getFilter());
        $this->assertEquals('Category 1.1', $item->getLabel());
        $this->assertEquals(4, $item->getValue());
        $this->assertEquals(2, $item->getCount());

        $item = $items[1];
        $this->assertInstanceOf(\Magento\Catalog\Model\Layer\Filter\Item::class, $item);
        $this->assertEquals('Category 1.2', $item->getLabel());
        $this->assertEquals(13, $item->getValue());
        $this->assertEquals(2, $item->getCount());
    }

    /**
     * Check that only children category of current category are aggregated
     *
     * @magentoDbIsolation disabled
     */
    public function testCategoryAggregation(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('cat', 3);
        $this->_model->apply($request);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get(\Magento\Framework\Registry::class)->registry(self::CURRENT_CATEGORY_FILTER);
        $this->assertInstanceOf(\Magento\Catalog\Model\Category::class, $category);
        $this->assertEquals(3, $category->getId());
        $metrics = $this->_model->getLayer()->getProductCollection()->getFacetedData('category');
        $this->assertIsArray($metrics);
        $actual = [];
        foreach ($metrics as $categoryId => $metric) {
            $actual[$categoryId] = $metric['count'];
        }
        $this->assertEquals(
            [
                4 => 2,
                13 => 2
            ],
            $actual
        );
    }
}
