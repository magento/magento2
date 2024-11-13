<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\_files\MultiselectSourceMock;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    protected function setUp(): void
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
     * @magentoDbIsolation disabled
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
        $this->assertCount(0, $result);

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

        /** @var \Magento\Catalog\Model\Product $product1 **/
        $product1 = $productRepository->getById(10);
        $product1->setStatus(Status::STATUS_ENABLED)->setWebsiteIds([]);
        $productRepository->save($product1);

        /** @var \Magento\Catalog\Model\Product $product2 **/
        $product2 = $productRepository->getById(20);
        $product2->setStatus(Status::STATUS_ENABLED);
        $productRepository->save($product2);

        $statusSelect = clone $select;
        $statusSelect->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(new \Magento\Framework\DB\Sql\Expression('COUNT(*)'));
        $this->assertEquals(0, $connection->fetchOne($statusSelect));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @magentoDbIsolation disabled
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

    /**
     * Test for indexing product attribute without "all store view" value
     *
     * @magentoDataFixture Magento/Catalog/_files/products_with_dropdown_attribute_without_all_store_view.php
     * @magentoDbIsolation disabled
     */
    public function testReindexSelectAttributeWithoutDefault()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreInterface $store */
        $store = $objectManager->get(StoreManagerInterface::class)
            ->getStore();
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute **/
        $attribute = $objectManager->get(\Magento\Eav\Model\Config::class)
            ->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'dropdown_without_default');
        /** @var AttributeOptionInterface $option */
        $option = $attribute->getOptions()[1];
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('test_attribute_dropdown_without_default', false, 1);
        $expected = [
            'entity_id' => $product->getId(),
            'attribute_id' => $attribute->getId(),
            'store_id'  => $store->getId(),
            'value' => $option->getValue(),
            'source_id' => $product->getId(),
        ];
        $connection = $this->productResource->getConnection();
        $select = $connection->select()->from($this->productResource->getTable('catalog_product_index_eav'))
            ->where('entity_id = ?', $product->getId())
            ->where('attribute_id = ?', $attribute->getId());

        $result = $connection->fetchRow($select);
        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute_with_source_model.php
     * @magentoDbIsolation disabled
     */
    public function testReindexMultiselectAttributeWithSourceModel()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(ProductRepositoryInterface::class);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attr **/
        $attr = $objectManager->get(\Magento\Eav\Model\Config::class)
            ->getAttribute('catalog_product', 'multiselect_attr_with_source');

        /** @var $sourceModel MultiselectSourceMock */
        $sourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            MultiselectSourceMock::class
        );
        $options = $sourceModel->getAllOptions();
        $product1Id = $options[0]['value'] * 10;
        $product2Id = $options[1]['value'] * 10;

        /** @var \Magento\Catalog\Model\Product $product1 **/
        $product1 = $productRepository->getById($product1Id);
        $product1->setSpecialFromDate(date('Y-m-d H:i:s'));
        $product1->setNewsFromDate(date('Y-m-d H:i:s'));
        $productRepository->save($product1);

        /** @var \Magento\Catalog\Model\Product $product2 **/
        $product2 = $productRepository->getById($product2Id);
        $product2->setSpecialFromDate(date('Y-m-d H:i:s'));
        $product2->setNewsFromDate(date('Y-m-d H:i:s'));
        $productRepository->save($product2);

        $this->_eavIndexerProcessor->reindexAll();
        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from($this->productResource->getTable('catalog_product_index_eav'))
            ->where('entity_id in (?)', [$product1Id, $product2Id])
            ->where('attribute_id = ?', $attr->getId());

        $result = $connection->fetchAll($select);
        $this->assertCount(3, $result);
    }
}
