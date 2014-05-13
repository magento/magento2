<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class RowTest
 */
class RowTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_state;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_processor;

    protected function setUp()
    {
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/row_fixture.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoAppArea frontend
     */
    public function testProductUpdate()
    {
        $this->markTestSkipped('Incomplete due to MAGETWO-21369');
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\CategoryFactory');
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Block\Product\ListProduct');

        $this->_processor->getIndexer()->setScheduled(false);
        $this->assertFalse(
            $this->_processor->getIndexer()->isScheduled(),
            'Indexer is in scheduled mode when turned to update on save mode'
        );
        $this->_processor->reindexAll();

        $this->_product->load(1);
        $this->_product->setName('Updated Product');
        $this->_product->save();

        $category = $categoryFactory->create()->load(9);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $productCollection */
        $productCollection = $layer->getProductCollection();
        $this->assertTrue(
            $productCollection->isEnabledFlat(),
            'Product collection is not using flat resource when flat is on'
        );

        $this->assertEquals(2, $productCollection->count(), 'Product collection items count must be exactly 2');

        foreach ($productCollection as $product) {
            /** @var $product \Magento\Catalog\Model\Product */
            if ($product->getId() == 1) {
                $this->assertEquals(
                    'Updated Product',
                    $product->getName(),
                    'Product name from flat does not match with updated name'
                );
            }
        }
    }
}
