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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Mage_Core_Model_StoreManagerInterface extends Mage_Core_Model_Store_ListInterface
{

    /**#@+
     * Available scope types
     */
    const SCOPE_TYPE_STORE   = 'store';
    const SCOPE_TYPE_GROUP   = 'group';
    const SCOPE_TYPE_WEBSITE = 'website';
    /**#@-*/

    /**
     * Retrieve application store object without Store_Exception
     *
     * @param string|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store
     */
    public function getSafeStore($id = null);

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode();
}
