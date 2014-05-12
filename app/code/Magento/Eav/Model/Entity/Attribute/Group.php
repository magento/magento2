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
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Group _getResource()
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Group getResource()
 * @method int getAttributeSetId()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setAttributeSetId(int $value)
 * @method string getAttributeGroupName()
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
class Group extends \Magento\Framework\Model\AbstractModel
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
    protected function _beforeSave()
    {
        if (!$this->getAttributeGroupCode()) {
            $groupName = $this->getAttributeGroupName();
            if ($groupName) {
                $this->setAttributeGroupCode(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($groupName)), '-'));
            }
        }
        return parent::_beforeSave();
    }
}
