<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Test class for Catalog attribute resource model.
 *
 * @see Magento\Catalog\Model\ResourceModel\Attribute
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(
            \Magento\Catalog\Model\ResourceModel\Attribute::class
        );
        $this->productResource = $this->objectManager->get(
            \Magento\Catalog\Model\ResourceModel\Product::class
        );
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->metadataPool = $this->objectManager->get(MetadataPool::class);
    }

    /**
     * Test to delete catalog eav attribute entity.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_eav_attribute.php
     */
    public function testDeleteEntity()
    {
        /* @var EavAttribute $attribute */
        $attribute = $this->objectManager->get(EavAttribute::class);
        $attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'text_attribute');
        $product = $this->productRepository->get('simple');

        $entityEavAttributeRow = $this->getEavEntityAttributeRow(
            $attribute->getEntityTypeId(),
            4,
            $attribute->getId()
        );
        $this->assertNotEmpty(
            $entityEavAttributeRow['entity_attribute_id'],
            'The record is absent in table `eav_entity_attribute` for `test_attribute`'
        );

        $entityAttributeValues = $this->getProductAttributeValues(
            $attribute,
            $product
        );
        $this->assertNotEmpty(
            $entityAttributeValues,
            'The attribute value is absent for product with ID = 1'
        );

        $attribute->setData('entity_attribute_id', $entityEavAttributeRow['entity_attribute_id']);
        $this->model->deleteEntity($attribute);

        $entityEavAttributeRow = $this->getEavEntityAttributeRow(
            $attribute->getEntityTypeId(),
            4,
            $attribute->getId()
        );
        $this->assertEmpty(
            $entityEavAttributeRow,
            'The record is not remove from table `eav_entity_attribute` for `test_attribute`'
        );

        $entityAttributeValues = $this->getProductAttributeValues(
            $attribute,
            $product
        );
        $this->assertEmpty(
            $entityAttributeValues,
            'The attribute value is not remove for product with ID = 1'
        );
    }

    /**
     * Retrieve eav attribute row.
     *
     * @param int $entityTypeId
     * @param int $attributeSetId
     * @param int $attributeId
     * @return array|false
     */
    private function getEavEntityAttributeRow($entityTypeId, $attributeSetId, $attributeId)
    {
        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from($this->productResource->getTable('eav_entity_attribute'))
            ->where('attribute_set_id=?', $attributeSetId)
            ->where('attribute_id=?', $attributeId)
            ->where('entity_type_id=?', $entityTypeId);

        return $connection->fetchRow($select);
    }

    /**
     * Retrieve product attribute values.
     *
     * @param EavAttribute $attribute
     * @param ProductInterface $product
     * @return array
     */
    private function getProductAttributeValues($attribute, $product)
    {
        $backendTable = $attribute->getBackend()->getTable();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $linkFieldValue = $product->getData($linkField);

        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from($this->productResource->getTable($backendTable))
            ->where('attribute_id=?', $attribute->getId())
            ->where($linkField . '=?', $linkFieldValue);

        return $connection->fetchAll($select);
    }
}
