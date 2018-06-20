<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\LockValidatorInterface;

/**
 * Catalog attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Type $eavEntityType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param LockValidatorInterface $lockValidator
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Type $eavEntityType,
        \Magento\Eav\Model\Config $eavConfig,
        LockValidatorInterface $lockValidator,
        $connectionName = null
    ) {
        $this->attrLockValidator = $lockValidator;
        $this->_eavConfig = $eavConfig;
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
                    'attribute_id = ?' => $object->getId(),
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

            $backendTable = $attribute->getBackend()->getTable();
            if ($backendTable) {
                $linkField = $this->getMetadataPool()
                    ->getMetadata(ProductInterface::class)
                    ->getLinkField();

                $select = $this->getConnection()->select()
                    ->from(['b' => $backendTable])
                    ->join(
                        ['e' => $attribute->getEntity()->getEntityTable()],
                        "b.$linkField = e.$linkField"
                    )->where('b.attribute_id = ?', $attribute->getId())
                    ->where('e.attribute_set_id = ?', $result['attribute_set_id']);

                $this->getConnection()->query($select->deleteFromSelect('b'));
            }
        }

        $condition = ['entity_attribute_id = ?' => $object->getEntityAttributeId()];
        $this->getConnection()->delete($this->getTable('eav_entity_attribute'), $condition);

        return $this;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
