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

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Service\V1\Data\Eav\AttributeSet;
use Magento\Framework\Exception\StateException;

/**
 * Class WriteService
 * Service to create/update/remove product attribute sets
 */
class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->setFactory = $setFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeSet $setData, $skeletonId)
    {
        if ($setData->getId()) {
            throw InputException::invalidFieldValue('id', $setData->getId());
        }

        $basicData = array(
            'attribute_set_name' => $setData->getName(),
            'sort_order' => $setData->getSortOrder(),
            'entity_type_id' => $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId(),
        );

        /** @var \Magento\Eav\Model\Entity\Attribute\Set $set */
        $set = $this->setFactory->create();
        foreach ($basicData as $key => $value) {
            $set->setData($key, $value);
        }
        try {
            $set->validate();
        } catch (\Magento\Eav\Exception $e) {
            throw new InputException($e->getMessage());
        }
        $set->save();
        //process skeleton data
        $skeletonId = intval($skeletonId);
        if (0 == $skeletonId) {
            throw InputException::invalidFieldValue('skeletonId', $skeletonId);
        }

        $skeletonSet = $this->setFactory->create()->load($skeletonId);
        $skeletonData = $skeletonSet->getData();
        if (empty($skeletonData)) {
            throw NoSuchEntityException::singleField('id', $skeletonId);
        }
        $set->initFromSkeleton($skeletonId);
        $set->save();

        return $set->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function update(AttributeSet $attributeSetData)
    {
        if (!$attributeSetData->getId()) {
            throw InputException::requiredField('id');
        }

        $attributeSetModel = $this->setFactory->create()->load($attributeSetData->getId());
        $requiredEntityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if (!$attributeSetModel->getId() || $attributeSetModel->getEntityTypeId() != $requiredEntityTypeId) {
            throw NoSuchEntityException::singleField('id', $attributeSetData->getId());
        }

        $attributeSetModel->setAttributeSetName($attributeSetData->getName());
        $attributeSetModel->setSortOrder($attributeSetData->getSortOrder());
        $attributeSetModel->save();
        return $attributeSetModel->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($attributeSetId)
    {
        $id = intval($attributeSetId);
        if (0 == $id) {
            throw InputException::invalidFieldValue('id', $id);
        }

        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->setFactory->create()->load($id);
        $defaultAttributeSetId =
            $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getDefaultAttributeSetId();
        $loadedData = $attributeSet->getData();
        if (empty($loadedData)) {
            throw NoSuchEntityException::singleField('id', $attributeSetId);
        }
        $productEntityId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if ($attributeSet->getEntityTypeId() != $productEntityId) {
            throw InputException::invalidFieldValue('id', $attributeSetId);
        }

        if ($attributeSetId == $defaultAttributeSetId) {
            throw new StateException('Default attribute set can not be deleted');
        }
        $attributeSet->delete();
        return true;
    }
}
