<?php
/**
 *
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
namespace Magento\Catalog\Service\V1\Product\AttributeGroup;

use \Magento\Catalog\Model\Product\Attribute\GroupFactory;
use \Magento\Catalog\Model\Product\Attribute\Group;
use Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder
     */
    protected $groupBuilder;

    /**
     * @param GroupFactory $groupFactory
     * @param AttributeGroupBuilder $groupBuilder
     */
    public function __construct(GroupFactory $groupFactory, AttributeGroupBuilder $groupBuilder)
    {
        $this->groupFactory = $groupFactory;
        $this->groupBuilder = $groupBuilder;
    }

    /**
     * {inheritdoc}
     */
    public function create($attributeSetId, \Magento\Catalog\Service\V1\Data\Eav\AttributeGroup $groupData)
    {
        try {
            /** @var Group $attributeGroup */
            $attributeGroup = $this->groupFactory->create();
            $attributeGroup->setAttributeGroupName($groupData->getName());
            $attributeGroup->setAttributeSetId($attributeSetId);
            $attributeGroup->save();
            return $this->groupBuilder->setId(
                $attributeGroup->getId()
            )->setName(
                $attributeGroup->getAttributeGroupName()
            )->create();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not create attribute group');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($groupId, \Magento\Catalog\Service\V1\Data\Eav\AttributeGroup $groupData)
    {
        /** @var Group $attributeGroup */
        $attributeGroup = $this->groupFactory->create();
        $attributeGroup->load($groupId);
        if (!$attributeGroup->getId()) {
            throw new NoSuchEntityException();
        }
        try {
            $attributeGroup->setId($groupData->getId());
            $attributeGroup->setAttributeGroupName($groupData->getName());
            $attributeGroup->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not update attribute group');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($groupId)
    {
        /** @var Group $attributeGroup */
        $attributeGroup = $this->groupFactory->create();
        $attributeGroup->load($groupId);
        if ($attributeGroup->hasSystemAttributes()) {
            throw new StateException('Attribute group that contains system attributes can not be deleted');
        }
        if (!$attributeGroup->getId()) {
            throw new NoSuchEntityException();
        }
        $attributeGroup->delete();
    }
}
