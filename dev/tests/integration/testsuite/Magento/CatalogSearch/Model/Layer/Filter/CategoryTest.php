<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogSearch\Model\Layer\Filter;

/**
 * Test class for \Magento\CatalogSearch\Model\Layer\Filter\Category.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->_category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $this->_category->load(5);
        $layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Category', [
                'data' => ['current_category' => $this->_category]
            ]);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogSearch\Model\Layer\Filter\Category', ['layer' => $layer]);
        $this->_model->setRequestVar('cat');
    }

    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister(self::CURRENT_CATEGORY_FILTER);
    }

    public function testGetResetValue()
    {
        $this->assertNull($this->_model->getResetValue());
    }

    public function testApplyNothing()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model->apply(
            $objectManager->get('Magento\TestFramework\Request'),
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\View\LayoutInterface'
            )->createBlock(
                'Magento\Framework\View\Element\Text'
            )
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->assertNull($objectManager->get('Magento\Framework\Registry')->registry(
                 self::CURRENT_CATEGORY_FILTER
            ));
    }

    public function testApply()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setParam('cat', 3);
        $this->_model->apply($request);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get('Magento\Framework\Registry')->registry(self::CURRENT_CATEGORY_FILTER);
        $this->assertInstanceOf('Magento\Catalog\Model\Category', $category);
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

    public function testGetItems()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setParam('cat', 4);
        $this->_model->apply($request);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $objectManager->get('Magento\Framework\Registry')->registry(self::CURRENT_CATEGORY_FILTER);
        $this->assertInstanceOf('Magento\Catalog\Model\Category', $category);
        $this->assertEquals(4, $category->getId());

        $items = $this->_model->getItems();

        $this->assertInternalType('array', $items);
        $this->assertEquals(1, count($items));

        /** @var $item \Magento\Catalog\Model\Layer\Filter\Item */
        $item = $items[0];

        $this->assertInstanceOf('Magento\Catalog\Model\Layer\Filter\Item', $item);
        $this->assertSame($this->_model, $item->getFilter());
        $this->assertEquals('Category 1.1.1', $item->getLabel());
        $this->assertEquals(5, $item->getValue());
        $this->assertEquals(1, $item->getCount());
    }
}
