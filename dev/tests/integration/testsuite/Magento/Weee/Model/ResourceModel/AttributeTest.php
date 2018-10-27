<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Weee\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\EntityManager\MetadataPool;

/**
<<<<<<< HEAD
 * Test class for Magento\Catalog\Model\ResourceModel\Attribute class
 * with backend model Magento\Weee\Model\Attribute\Backend\Weee\Tax.
=======
 * Test class for Catalog attribute resource model.
>>>>>>> upstream/2.2-develop
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

<<<<<<< HEAD
    /**
     * @inheritdoc
     */
=======
>>>>>>> upstream/2.2-develop
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
     * Retrieve eav attribute row.
     *
     * @param int $entityTypeId
     * @param int $attributeSetId
     * @param int $attributeId
     * @return array|false
     */
<<<<<<< HEAD
    private function getEavEntityAttributeRow(int $entityTypeId, int $attributeSetId, int $attributeId)
=======
    private function getEavEntityAttributeRow($entityTypeId, $attributeSetId, $attributeId)
>>>>>>> upstream/2.2-develop
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
     * Test to delete entity attribute with type "Fixed Product Tax".
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     * @return void
     */
<<<<<<< HEAD
    public function testDeleteEntityFixedTax() : void
=======
    public function testDeleteEntityFixedTax()
>>>>>>> upstream/2.2-develop
    {
        /* @var EavAttribute $attribute */
        $attribute = $this->objectManager->get(EavAttribute::class);
        $attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, 'fixed_product_attribute');

        $entityEavAttributeRow = $this->getEavEntityAttributeRow(
<<<<<<< HEAD
            (int)$attribute->getEntityTypeId(),
            4,
            (int)$attribute->getId()
=======
            $attribute->getEntityTypeId(),
            4,
            $attribute->getId()
>>>>>>> upstream/2.2-develop
        );
        $this->assertNotEmpty(
            $entityEavAttributeRow['entity_attribute_id'],
            'The record is absent in table `eav_entity_attribute` for `fixed_product_attribute`'
        );

        $attribute->setData('entity_attribute_id', $entityEavAttributeRow['entity_attribute_id']);
        $this->model->deleteEntity($attribute);

        $entityEavAttributeRow = $this->getEavEntityAttributeRow(
<<<<<<< HEAD
            (int)$attribute->getEntityTypeId(),
            4,
            (int)$attribute->getId()
        );
        $this->assertEmpty(
            $entityEavAttributeRow,
            'The record was not removed from table `eav_entity_attribute` for `fixed_product_attribute`'
=======
            $attribute->getEntityTypeId(),
            4,
            $attribute->getId()
        );
        $this->assertEmpty(
            $entityEavAttributeRow,
            'The record is not remove from table `eav_entity_attribute` for `fixed_product_attribute`'
>>>>>>> upstream/2.2-develop
        );
    }
}
