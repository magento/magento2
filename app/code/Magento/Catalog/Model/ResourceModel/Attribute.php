<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Catalog\Model\ResourceModel\Attribute\RemoveProductAttributeData;
use Magento\Framework\App\ObjectManager;

/**
 * Catalog attribute resource model
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Entity\Attribute
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
     * @var RemoveProductAttributeData|null
     */
    private $removeProductAttributeData;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Type $eavEntityType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param LockValidatorInterface $lockValidator
     * @param string|null $connectionName
     * @param RemoveProductAttributeData|null $removeProductAttributeData
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Type $eavEntityType,
        \Magento\Eav\Model\Config $eavConfig,
        LockValidatorInterface $lockValidator,
        $connectionName = null,
        RemoveProductAttributeData $removeProductAttributeData = null
    ) {
        $this->attrLockValidator = $lockValidator;
        $this->_eavConfig = $eavConfig;
        $this->removeProductAttributeData = $removeProductAttributeData ?? ObjectManager::getInstance()
            ->get(RemoveProductAttributeData::class);

        parent::__construct($context, $storeManager, $eavEntityType, $connectionName);
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
        ) && \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL != $origData['is_global']
        ) {
            $attributeStoreIds = array_keys($this->_storeManager->getStores());
            if (!empty($attributeStoreIds)) {
                $delCondition = [
                    'attribute_id = ?' => (int)$object->getId(),
                    'store_id IN(?)' => $attributeStoreIds,
                ];
                $this->getConnection()->delete($object->getBackendTable(), $delCondition);
            }
        }

        return $this;
    }

    /**
     * Delete entity
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteEntity(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $result = $this->getEntityAttribute($object->getEntityAttributeId());
        if ($result) {
            $attribute = $this->_eavConfig->getAttribute(
                $object->getEntityTypeId(),
                $result['attribute_id']
            );

            try {
                $this->attrLockValidator->validate($attribute, $result['attribute_set_id']);
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attribute \'%1\' is locked. %2', $attribute->getAttributeCode(), $exception->getMessage())
                );
            }

            $this->removeProductAttributeData->removeData($object, (int)$result['attribute_set_id']);
        }

        $condition = ['entity_attribute_id = ?' => $object->getEntityAttributeId()];
        $this->getConnection()->delete($this->getTable('eav_entity_attribute'), $condition);

        return $this;
    }
}
