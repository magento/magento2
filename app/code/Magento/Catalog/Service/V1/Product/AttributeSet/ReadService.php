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
use Magento\Framework\Exception\NoSuchEntityException;

class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $setCollectionFactory;

    /**
     * @var Data\Eav\AttributeSetBuilder
     */
    protected $attributeSetBuilder;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Eav\AttributeBuilder
     */
    protected $attributeBuilder;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Data\Eav\AttributeSetBuilder $attributeSetBuilder
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Collection $attributeCollection
     * @param Data\Eav\AttributeBuilder $attributeBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Service\V1\Data\Eav\AttributeSetBuilder $attributeSetBuilder,
        \Magento\Eav\Model\Resource\Entity\Attribute\Collection $attributeCollection,
        \Magento\Catalog\Service\V1\Data\Eav\AttributeBuilder $attributeBuilder
    ) {
        $this->setFactory = $setFactory;
        $this->setCollectionFactory = $setCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeSetBuilder = $attributeSetBuilder;
        $this->attributeCollection = $attributeCollection;
        $this->attributeBuilder = $attributeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $sets = array();

        $attributeSetsCollection = $this->setCollectionFactory->create()
            ->setEntityTypeFilter($this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId())
            ->load();

        /** @var $attributeSet \Magento\Eav\Model\Resource\Entity\Attribute\Set */
        foreach ($attributeSetsCollection as $attributeSet) {
            $this->attributeSetBuilder->setId($attributeSet->getId());
            $this->attributeSetBuilder->setName($attributeSet->getAttributeSetName());
            $this->attributeSetBuilder->setSortOrder($attributeSet->getSortOrder());
            $sets[] = $this->attributeSetBuilder->create();
        }

        return $sets;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo($attributeSetId)
    {
        $attributeSet = $this->setFactory->create()->load($attributeSetId);
        $requiredEntityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if (!$attributeSet->getId() || $attributeSet->getEntityTypeId() != $requiredEntityTypeId) {
            // Attribute set does not exist
            throw NoSuchEntityException::singleField('attributeSetId', $attributeSetId);
        }
        $attrSetDataObject = $this->attributeSetBuilder->setId($attributeSet->getId())
            ->setName($attributeSet->getAttributeSetName())
            ->setSortOrder($attributeSet->getSortOrder())
            ->create();
        return $attrSetDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeList($attributeSetId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->setFactory->create()->load($attributeSetId);
        $requiredEntityTypeId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if (!$attributeSet->getId() || $attributeSet->getEntityTypeId() != $requiredEntityTypeId) {
            // Attribute set does not exist
            throw NoSuchEntityException::singleField('attributeSetId', $attributeSetId);
        }
        $attributeCollection = $this->attributeCollection->setAttributeSetFilter($attributeSet->getId())->load();

        $attributes = array();
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            $attributes[] = $this->attributeBuilder->setId($attribute->getAttributeId())
                ->setCode($attribute->getAttributeCode())
                ->setFrontendLabel($attribute->getData('frontend_label'))
                ->setDefaultValue($attribute->getDefaultValue())
                ->setIsRequired((boolean)$attribute->getData('is_required'))
                ->setIsUserDefined((boolean)$attribute->getData('is_user_defined'))
                ->setFrontendInput($attribute->getData('frontend_input'))
                ->create();
        }
        return $attributes;

    }
}
