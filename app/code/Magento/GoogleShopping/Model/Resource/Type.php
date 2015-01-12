<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Resource;

/**
 * Google Content Type resource model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('googleshopping_types', 'type_id');
    }

    /**
     * Return Type ID by Attribute Set Id and target country
     *
     * @param \Magento\GoogleShopping\Model\Type $model
     * @param int $attributeSetId Attribute Set
     * @param string $targetCountry Two-letters country ISO code
     * @return \Magento\GoogleShopping\Model\Type
     */
    public function loadByAttributeSetIdAndTargetCountry($model, $attributeSetId, $targetCountry)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable()
        )->where(
            'attribute_set_id=?',
            $attributeSetId
        )->where(
            'target_country=?',
            $targetCountry
        );

        $data = $this->_getReadAdapter()->fetchRow($select);
        $data = is_array($data) ? $data : [];
        $model->setData($data);
        return $model;
    }
}
