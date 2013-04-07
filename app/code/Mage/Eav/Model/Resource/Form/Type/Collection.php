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
 * @category    Mage
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Eav Form Type Resource Collection
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Resource_Form_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Eav_Model_Form_Type', 'Mage_Eav_Model_Resource_Form_Type');
    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('type_id', 'label');
    }

    /**
     * Add Entity type filter to collection
     *
     * @param Mage_Eav_Model_Entity_Type|int $entity
     * @return Mage_Eav_Model_Resource_Form_Type_Collection
     */
    public function addEntityTypeFilter($entity)
    {
        if ($entity instanceof Mage_Eav_Model_Entity_Type) {
            $entity = $entity->getId();
        }

        $this->getSelect()
            ->join(
                array('form_type_entity' => $this->getTable('eav_form_type_entity')),
                'main_table.type_id = form_type_entity.type_id',
                array())
            ->where('form_type_entity.entity_type_id = ?', $entity);

        return $this;
    }
}
