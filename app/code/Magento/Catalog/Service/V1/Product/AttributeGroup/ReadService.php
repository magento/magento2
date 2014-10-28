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

use \Magento\Catalog\Service\V1\Data;
use \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory as AttributeGroupCollectionFactory;
use \Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use \Magento\Framework\Exception\NoSuchEntityException;

class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupListFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Eav\AttributeGroupBuilder
     */
    protected $groupBuilder;

    /**
     * @param AttributeGroupCollectionFactory $groupListFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param Data\Eav\AttributeGroupBuilder $groupBuilder
     */
    public function __construct(
        AttributeGroupCollectionFactory $groupListFactory,
        AttributeSetFactory $attributeSetFactory,
        Data\Eav\AttributeGroupBuilder $groupBuilder
    ) {
        $this->groupListFactory = $groupListFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->groupBuilder = $groupBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($attributeSetId)
    {
        if (!$this->attributeSetFactory->create()->load($attributeSetId)->getId()) {
            throw NoSuchEntityException::singleField('attributeSetId', $attributeSetId);
        }

        $collection = $this->groupListFactory->create();
        $collection->setAttributeSetFilter($attributeSetId);
        $collection->setSortOrder();

        $groups = array();

        /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
        foreach ($collection->getItems() as $group) {
            $this->groupBuilder->setId(
                $group->getId()
            )->setName(
                $group->getAttributeGroupName()
            );
            $groups[] = $this->groupBuilder->create();
        }
        return $groups;
    }
}
