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
namespace Magento\Catalog\Model\Resource;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;

/**
 * Catalog attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Eav\Model\Resource\Entity\Attribute
{
    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var LockValidatorInterface
     */
    protected $attrLockValidator;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Resource\Entity\Type $eavEntityType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param LockValidatorInterface $lockValidator
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Resource\Entity\Type $eavEntityType,
        \Magento\Eav\Model\Config $eavConfig,
        LockValidatorInterface $lockValidator
    ) {
        $this->attrLockValidator = $lockValidator;
        $this->_eavConfig = $eavConfig;
        parent::__construct($resource, $storeManager, $eavEntityType);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $applyTo = $object->getApplyTo();
        if (is_array($applyTo)) {
            $object->setApplyTo(implode(',', $applyTo));
        }
        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_clearUselessAttributeValues($object);
        return parent::_afterSave($object);
    }

    /**
     * Clear useless attribute values
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _clearUselessAttributeValues(\Magento\Framework\Model\AbstractModel $object)
    {
        $origData = $object->getOrigData();

        if ($object->isScopeGlobal() && isset(
            $origData['is_global']
        ) && \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL != $origData['is_global']
        ) {
            $attributeStoreIds = array_keys($this->_storeManager->getStores());
            if (!empty($attributeStoreIds)) {
                $delCondition = array(
                    'entity_type_id=?' => $object->getEntityTypeId(),
                    'attribute_id = ?' => $object->getId(),
                    'store_id IN(?)' => $attributeStoreIds
                );
                $this->_getWriteAdapter()->delete($object->getBackendTable(), $delCondition);
            }
        }

        return $this;
    }

    /**
     * Delete entity
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function deleteEntity(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('eav_entity_attribute')
        )->where(
            'entity_attribute_id = ?',
            (int)$object->getEntityAttributeId()
        );
        $result = $this->_getReadAdapter()->fetchRow($select);

        if ($result) {
            $attribute = $this->_eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $result['attribute_id']
            );

            try {
                $this->attrLockValidator->validate($attribute, $result['attribute_set_id']);
            } catch (\Magento\Framework\Model\Exception $exception) {
                throw new \Magento\Framework\Model\Exception(
                    __("Attribute '%1' is locked. ", $attribute->getAttributeCode()) . $exception->getMessage()
                );
            }

            $backendTable = $attribute->getBackend()->getTable();
            if ($backendTable) {
                $select = $this->_getWriteAdapter()->select()->from(
                    $attribute->getEntity()->getEntityTable(),
                    'entity_id'
                )->where(
                    'attribute_set_id = ?',
                    $result['attribute_set_id']
                );

                $clearCondition = array(
                    'entity_type_id =?' => $attribute->getEntityTypeId(),
                    'attribute_id =?' => $attribute->getId(),
                    'entity_id IN (?)' => $select
                );
                $this->_getWriteAdapter()->delete($backendTable, $clearCondition);
            }
        }

        $condition = array('entity_attribute_id = ?' => $object->getEntityAttributeId());
        $this->_getWriteAdapter()->delete($this->getTable('eav_entity_attribute'), $condition);

        return $this;
    }
}
