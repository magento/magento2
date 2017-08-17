<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SourceTest
 * @magentoAppIsolation enabled
 */
class SourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source
     */
    protected $source;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_eavIndexerProcessor;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->source = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::class
        );

        $this->productResource = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product::class
        );

        $this->_eavIndexerProcessor = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Indexer\Product\Eav\Processor::class
        );
    }

    /**
     * Test reindex for configurable product with both disabled and enabled variations.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testReindexEntitiesForConfigurableProduct()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(ProductRepositoryInterface::class);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attr **/
        $attr = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class)
           ->getAttribute('catalog_product', 'test_configurable');
        $attr->setIsFilterable(1)->save();

        $this->_eavIndexerProcessor->reindexAll();

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $options **/
        $options = $objectManager->create(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
        );
        $options->setAttributeFilter($attr->getId())->load();
        $optionIds = $options->getAllIds();

        $connection = $this->productResource->getConnection();

        $select = $connection->select()->from($this->productResource->getTable('catalog_product_index_eav'))
            ->where('entity_id = ?', 1)
            ->where('attribute_id = ?', $attr->getId())
            ->where('value IN (?)', $optionIds);

        $result = $connection->fetchAll($select);
        $this->assertCount(2, $result);

        /** @var \Magento\Catalog\Model\Product $product1 **/
        $product1 = $productRepository->getById(10);
        $product1->setStatus(Status::STATUS_DISABLED);
        $productRepository->save($product1);

        /** @var \Magento\Catalog\Model\Product $product2 **/
        $product2 = $productRepository->getById(20);
        $product2->setStatus(Status::STATUS_DISABLED);
        $productRepository->save($product2);

        $result = $connection->fetchAll($select);
        $this->assertCount(0, $result);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testReindexMultiselectAttribute()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(ProductRepositoryInterface::class);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attr **/
        $attr = $objectManager->get(\Magento\Eav\Model\Config::class)
           ->getAttribute('catalog_product', 'multiselect_attribute');

        /** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
        $options = $objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class);
        $options->setAttributeFilter($attr->getId());
        $optionIds = $options->getAllIds();
        $product1Id = $optionIds[0] * 10;
        $product2Id = $optionIds[1] * 10;

        /** @var \Magento\Catalog\Model\Product $product1 **/
        $product1 = $productRepository->getById($product1Id);
        $product1->setSpecialFromDate(date('Y-m-d H:i:s'));
        $product1->setNewsFromDate(date('Y-m-d H:i:s'));
        $productRepository->save($product1);

        /** @var \Magento\Catalog\Model\Product $product2 **/
        $product2 = $productRepository->getById($product2Id);
        $product1->setSpecialFromDate(date('Y-m-d H:i:s'));
        $product1->setNewsFromDate(date('Y-m-d H:i:s'));
        $productRepository->save($product2);

        $this->_eavIndexerProcessor->reindexAll();
        $connection = $this->productResource->getConnection();
        $select = $connection->select()->from($this->productResource->getTable('catalog_product_index_eav'))
            ->where('entity_id in (?)', [$product1Id, $product2Id])
            ->where('attribute_id = ?', $attr->getId());

        $result = $connection->fetchAll($select);
        $this->assertCount(3, $result);
    }
}
