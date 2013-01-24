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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout update collection model
 */
class Mage_Core_Model_Resource_Layout_Link_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Core_Model_Layout_Link', 'Mage_Core_Model_Resource_Layout_Link');
    }

    /**
     * Join with layout update table
     *
     * @param array $fields
     * @return Mage_Core_Model_Resource_Layout_Link_Collection
     */
    protected function _joinWithUpdate($fields = array())
    {
        $flagName = 'joined_with_update_table';
        if (!$this->getFlag($flagName)) {
            $this->getSelect()
                ->join(
                    array('update' => $this->getTable('core_layout_update')),
                    'update.layout_update_id = main_table.layout_update_id',
                    array($fields)
                );
            $this->setFlag($flagName, true);
        }

        return $this;
    }

    /**
     * Filter by temporary flag
     *
     * @param bool $isTemporary
     * @return Mage_Core_Model_Resource_Layout_Update_Collection
     */
    public function addTemporaryFilter($isTemporary)
    {
        $this->addFieldToFilter('main_table.is_temporary', $isTemporary ? 1 : 0);
        return $this;
    }

    /**
     * Get links for layouts that are older then specified number of days
     *
     * @param $days
     * @return Mage_Core_Model_Resource_Layout_Update_Collection
     */
    public function addUpdatedDaysBeforeFilter($days)
    {
        $datetime = new DateTime();
        $storeInterval = new DateInterval('P' . $days . 'D');
        $datetime->sub($storeInterval);
        $formattedDate = $this->formatDate($datetime->getTimestamp());

        $this->_joinWithUpdate();
        $this->addFieldToFilter('update.updated_at', array('notnull' => true))
            ->addFieldToFilter('update.updated_at', array('lt' => $formattedDate));

        return $this;
    }
}
