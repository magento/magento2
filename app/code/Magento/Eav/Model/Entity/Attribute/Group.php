<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Group _getResource()
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Group getResource()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setAttributeSetId(int $value)
 * @method \Magento\Eav\Model\Entity\Attribute\Group setAttributeGroupName(string $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setSortOrder(int $value)
 * @method int getDefaultId()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setDefaultId(int $value)
 * @method string getAttributeGroupCode()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setAttributeGroupCode(string $value)
 * @method string getTabGroupCode()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setTabGroupCode(string $value)
 */
class Group extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Eav\Api\Data\AttributeGroupInterface
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Entity\Attribute\Group');
    }

    /**
     * Checks if current attribute group exists
     *
     * @return bool
     */
    public function itemExists()
    {
        return $this->_getResource()->itemExists($this);
    }

    /**
     * Delete groups
     *
     * @return $this
     */
    public function deleteGroups()
    {
        return $this->_getResource()->deleteGroups($this);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getAttributeGroupCode()) {
            $groupName = $this->getAttributeGroupName();
            if ($groupName) {
                $this->setAttributeGroupCode(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($groupName)), '-'));
            }
        }
        return parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnoreStart
     */
    public function getAttributeGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeGroupName()
    {
        return $this->getData(self::GROUP_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetId()
    {
        return $this->getData(self::ATTRIBUTE_SET_ID);
    }
    //@codeCoverageIgnoreEnd
}
