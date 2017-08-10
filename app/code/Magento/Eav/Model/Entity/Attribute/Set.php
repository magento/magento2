<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Eav attribute set model
 *
 * @method int getEntityTypeId()
 * @method \Magento\Eav\Model\Entity\Attribute\Set setEntityTypeId(int $value)
 * @method string getAttributeSetName()
 * @method \Magento\Eav\Model\Entity\Attribute\Set setAttributeSetName(string $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Entity\Attribute\Set setSortOrder(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Set extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Eav\Api\Data\AttributeSetInterface
{
    /**#@+
     * Constants
     */
    const KEY_ATTRIBUTE_SET_ID = 'attribute_set_id';
    const KEY_ATTRIBUTE_SET_NAME = 'attribute_set_name';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_ENTITY_TYPE_ID = 'entity_type_id';
    /**#@-*/

    /**#@-*/
    protected $_resource;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_entity_attribute_set';

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\GroupFactory
     */
    protected $_attrGroupFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_resourceAttribute;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param GroupFactory $attrGroupFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceAttribute
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $attrGroupFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceAttribute,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_eavConfig = $eavConfig;
        $this->_attrGroupFactory = $attrGroupFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_resourceAttribute = $resourceAttribute;
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class);
    }

    /**
     * Init attribute set from skeleton (another attribute set)
     *
     * @param int $skeletonId
     * @return $this
     */
    public function initFromSkeleton($skeletonId)
    {
        $groups = $this->_attrGroupFactory->create()->getResourceCollection()->setAttributeSetFilter(
            $skeletonId
        )->load();

        $newGroups = [];
        foreach ($groups as $group) {
            $newGroup = clone $group;
            $newGroup->setId(null)->setAttributeSetId($this->getId())->setDefaultId($group->getDefaultId());

            $groupAttributesCollection = $this->_attributeFactory
                ->create()
                ->getResourceCollection()
                ->setAttributeGroupFilter(
                    $group->getId()
                )->load();

            $newAttributes = [];
            foreach ($groupAttributesCollection as $attribute) {
                $newAttribute = $this->_attributeFactory->create()
                    ->setId($attribute->getId())
                    //->setAttributeGroupId($newGroup->getId())
                    ->setAttributeSetId($this->getId())
                    ->setEntityTypeId($this->getEntityTypeId())
                    ->setSortOrder($attribute->getSortOrder());
                $newAttributes[] = $newAttribute;
            }
            $newGroup->setAttributes($newAttributes);
            $newGroups[] = $newGroup;
        }
        $this->setGroups($newGroups);

        return $this;
    }

    /**
     * Collect data for save
     *
     * @param array $data
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function organizeData($data)
    {
        $modelGroupArray = [];
        $modelAttributeArray = [];
        $attributeIds = [];
        if ($data['attributes']) {
            $ids = [];
            foreach ($data['attributes'] as $attribute) {
                $ids[] = $attribute[0];
            }
            $attributeIds = $this->_resourceAttribute->getValidAttributeIds($ids);
        }
        if ($data['groups']) {
            foreach ($data['groups'] as $group) {
                $modelGroup = $this->initGroupModel($group);

                if ($data['attributes']) {
                    foreach ($data['attributes'] as $attribute) {
                        if ($attribute[1] == $group[0] && in_array($attribute[0], $attributeIds)) {
                            $modelAttribute = $this->_attributeFactory->create();
                            $modelAttribute->setId(
                                $attribute[0]
                            )->setAttributeGroupId(
                                $attribute[1]
                            )->setAttributeSetId(
                                $this->getId()
                            )->setEntityTypeId(
                                $this->getEntityTypeId()
                            )->setSortOrder(
                                $attribute[2]
                            );
                            $modelAttributeArray[] = $modelAttribute;
                        }
                    }
                    $modelGroup->setAttributes($modelAttributeArray);
                    $modelAttributeArray = [];
                }
                $modelGroupArray[] = $modelGroup;
            }
            $this->setGroups($modelGroupArray);
        }

        if ($data['not_attributes']) {
            $modelAttributeArray = [];
            $data['not_attributes'] = array_filter($data['not_attributes']);
            foreach ($data['not_attributes'] as $entityAttributeId) {
                $entityAttribute = $this->_resourceAttribute->getEntityAttribute($entityAttributeId);
                if (!$entityAttribute) {
                    throw new LocalizedException(__('Entity attribute with id "%1" not found', $entityAttributeId));
                }
                $modelAttribute = $this->_eavConfig->getAttribute(
                    $this->getEntityTypeId(),
                    $entityAttribute['attribute_id']
                );
                $modelAttribute->setEntityAttributeId($entityAttributeId);
                $modelAttributeArray[] = $modelAttribute;
            }
            $this->setRemoveAttributes($modelAttributeArray);
        }

        if ($data['removeGroups']) {
            $modelGroupArray = [];
            foreach ($data['removeGroups'] as $groupId) {
                $modelGroup = $this->_attrGroupFactory->create();
                $modelGroup->setId($groupId);

                $modelGroupArray[] = $modelGroup;
            }
            $this->setRemoveGroups($modelGroupArray);
        }
        $this->setAttributeSetName($data['attribute_set_name'])->setEntityTypeId($this->getEntityTypeId());

        return $this;
    }

    /**
     * @param array $group
     * @return Group
     */
    private function initGroupModel($group)
    {
        $modelGroup = $this->_attrGroupFactory->create();
        $modelGroup->setId(
            is_numeric($group[0]) && $group[0] > 0 ? $group[0] : null
        )->setAttributeGroupName(
            $group[1]
        )->setAttributeSetId(
            $this->getId()
        )->setSortOrder(
            $group[2]
        );
        if ($modelGroup->getId()) {
            $group = $this->_attrGroupFactory->create()->load($modelGroup->getId());
            if ($group->getId()) {
                $modelGroup->setAttributeGroupCode($group->getAttributeGroupCode());
            }
        }
        return $modelGroup;
    }

    /**
     * Validate attribute set name
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validate()
    {
        $attributeSetName = $this->getAttributeSetName();
        if ($attributeSetName == '') {
            throw new LocalizedException(__('Attribute set name is empty.'));
        }

        if (!$this->_getResource()->validate($this, $attributeSetName)) {
            throw new LocalizedException(__('An attribute set named "%1" already exists.', $attributeSetName));
        }

        return true;
    }

    /**
     * Add set info to attributes
     *
     * @param string|Type $entityType
     * @param array $attributes
     * @param int $setId
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addSetInfo($entityType, array $attributes, $setId = null)
    {
        $attributeIds = [];
        $entityType = $this->_eavConfig->getEntityType($entityType);
        foreach ($attributes as $attribute) {
            $attribute = $this->_eavConfig->getAttribute($entityType, $attribute);
            if ($setId && is_array($attribute->getAttributeSetInfo($setId))) {
                continue;
            }
            if (!$attribute->getAttributeId()) {
                continue;
            }
            $attributeIds[] = $attribute->getAttributeId();
        }

        if ($attributeIds) {
            $setInfo = $this->_getResource()->getSetInfo($attributeIds, $setId);

            foreach ($attributes as $attribute) {
                $attribute = $this->_eavConfig->getAttribute($entityType, $attribute);
                if (!$attribute->getAttributeId()) {
                    continue;
                }
                if (!in_array($attribute->getAttributeId(), $attributeIds)) {
                    continue;
                }
                if (is_numeric($setId)) {
                    $attributeSetInfo = $attribute->getAttributeSetInfo();
                    if (!is_array($attributeSetInfo)) {
                        $attributeSetInfo = [];
                    }
                    if (isset($setInfo[$attribute->getAttributeId()][$setId])) {
                        $attributeSetInfo[$setId] = $setInfo[$attribute->getAttributeId()][$setId];
                    }
                    $attribute->setAttributeSetInfo($attributeSetInfo);
                } else {
                    if (isset($setInfo[$attribute->getAttributeId()])) {
                        $attribute->setAttributeSetInfo($setInfo[$attribute->getAttributeId()]);
                    } else {
                        $attribute->setAttributeSetInfo([]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Return default Group Id for current or defined Attribute Set
     *
     * @param int $setId
     * @return int|null
     */
    public function getDefaultGroupId($setId = null)
    {
        if ($setId === null) {
            $setId = $this->getId();
        }

        return $setId ? $this->_getResource()->getDefaultGroupId($setId) : null;
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @deprecated because resource models should be used directly
     */
    protected function _getResource()
    {
        return $this->_resource ?: parent::_getResource();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnoreStart
     */
    public function getAttributeSetId()
    {
        return $this->getData(self::KEY_ATTRIBUTE_SET_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetName()
    {
        return $this->getData(self::KEY_ATTRIBUTE_SET_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->getData(self::KEY_ENTITY_TYPE_ID);
    }

    /**
     * Set attribute set name.
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->setData('attribute_set_name', $name);
    }

    /**
     * Set attribute set ID
     *
     * @param int $attributeSetId
     * @return $this
     */
    public function setAttributeSetId($attributeSetId)
    {
        return $this->setData(self::KEY_ATTRIBUTE_SET_ID, $attributeSetId);
    }

    /**
     * Set attribute set name
     *
     * @param string $attributeSetName
     * @return $this
     */
    public function setAttributeSetName($attributeSetName)
    {
        return $this->setData(self::KEY_ATTRIBUTE_SET_NAME, $attributeSetName);
    }

    /**
     * Set attribute set sort order index
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Set attribute set entity type id
     *
     * @param int $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId)
    {
        return $this->setData(self::KEY_ENTITY_TYPE_ID, $entityTypeId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Eav\Api\Data\AttributeSetExtensionInterface|null|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Eav\Api\Data\AttributeSetExtensionInterface|null $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Eav\Api\Data\AttributeSetExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
