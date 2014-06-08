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
namespace Magento\Catalog\Service\V1\Product\AttributeSet;

use Magento\Catalog\Service\V1\Data;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class AttributeService implements AttributeServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected $attributeResource;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $entityTypeFactory;

    /**
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute $attributeResource
     * @param \Magento\Eav\Model\ConfigFactory $entityTypeFactory
     */
    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\ConfigFactory $entityTypeFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute $attributeResource
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->groupFactory = $groupFactory;
        $this->setFactory = $setFactory;
        $this->attributeResource = $attributeResource;
        $this->entityTypeFactory = $entityTypeFactory;
    }

    /**
     * Add attribute to attribute set and group
     *
     * @param int $attributeSetId
     * @param Data\Eav\AttributeSet\Attribute $data
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     */
    public function addAttribute($attributeSetId, \Magento\Catalog\Service\V1\Data\Eav\AttributeSet\Attribute $data)
    {
        $attributeSet = $this->setFactory->create()->load($attributeSetId);
        if (!$attributeSet->getId()) {
            throw new InputException('Attribute set does not exist');
        }

        $setEntityType = $this->entityTypeFactory->create()->getEntityType($attributeSet->getEntityTypeId());
        if ($setEntityType->getEntityTypeCode() != \Magento\Catalog\Model\Product::ENTITY) {
            throw new InputException('Wrong attribute set id provided');
        }

        if (!$this->groupFactory->create()->load($data->getAttributeGroupId())->getId()) {
            throw new InputException('Attribute group does not exist');
        }

        $attribute = $this->attributeFactory->create();
        if (!$attribute->load($data->getAttributeId())->getId()) {
            throw new InputException('Attribute does not exist');
        }

        $attribute->setId($data->getAttributeId());
        $attribute->setEntityTypeId($setEntityType->getId());
        $attribute->setAttributeSetId($attributeSetId);
        $attribute->setAttributeGroupId($data->getAttributeGroupId());
        $attribute->setSortOrder($data->getSortOrder());

        $this->attributeResource->saveInSetIncluding($attribute);
        return $attribute->loadEntityAttributeIdBySet()->getData('entity_attribute_id');
    }

    /**
     * @param string $attributeSetId
     * @param string $attributeId
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteAttribute($attributeSetId, $attributeId)
    {
        $attributeSet = $this->setFactory->create()->load($attributeSetId);
        if (!$attributeSet->getId()) {
            // Attribute set does not exist
            throw NoSuchEntityException::singleField('attributeSetId', $attributeSetId);
        }
        // check that attribute set has type catalog_product
        $setEntityType = $this->entityTypeFactory->create()->getEntityType($attributeSet->getEntityTypeId());
        if ($setEntityType->getEntityTypeCode() != \Magento\Catalog\Model\Product::ENTITY) {
            throw new InputException('Attribute with wrong attribute type is provided');
        }

        // check if attribute with requested id exists
        $attribute = $this->attributeFactory->create()->load($attributeId);
        if (!$attribute->getId()) {
            // Attribute set does not exist
            throw NoSuchEntityException::singleField('attributeId', $attributeId);
        }
        // check if attribute is in set
        $attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();
        if (!$attribute->getEntityAttributeId()) {
            throw  new InputException('Requested attribute is not in requested attribute set.');
        }
        if (!$attribute->getIsUserDefined()) {
            throw new StateException('System attribute can not be deleted');
        }
        $attribute->deleteEntity();
        return true;
    }
}
