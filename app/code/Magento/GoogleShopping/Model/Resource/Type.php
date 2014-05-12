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
        $data = is_array($data) ? $data : array();
        $model->setData($data);
        return $model;
    }
}
