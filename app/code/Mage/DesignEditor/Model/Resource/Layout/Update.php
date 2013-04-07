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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * VDE area layout update resource model
 */
class Mage_DesignEditor_Model_Resource_Layout_Update extends Mage_Core_Model_Resource_Layout_Update
{
    /**
     * Get select to fetch updates by handle
     *
     * @param bool $loadAllUpdates
     * @return Varien_Db_Select
     */
    protected function _getFetchUpdatesByHandleSelect($loadAllUpdates = false)
    {
        // always load all layout updates in vde mode
        $loadAllUpdates = true;
        return parent::_getFetchUpdatesByHandleSelect($loadAllUpdates);
    }

    /**
     * Make temporary updates for given theme and given stores permanent
     *
     * @param int $themeId
     * @param array $storeIds
     */
    public function makeTemporaryLayoutUpdatesPermanent($themeId, array $storeIds)
    {
        $this->_getWriteAdapter()->update($this->getTable('core_layout_link'),
            array('is_temporary' => 0),
            array(
                'theme_id = ?'   => $themeId,
                'store_id IN(?)' => $storeIds,
            )
        );
    }
}
