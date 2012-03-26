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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base class for Rss controllers, where admin login required for some actions
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Rss_Controller_AdminhtmlAbstract extends Mage_Core_Controller_Front_Action
{
    /**
     * Returns map of action to acl paths, needed to check user's access to a specific action
     *
     * @return array
     */
    abstract protected function _getAdminAclMap();

    /**
     * Controller predispatch method to change area for a specific action
     *
     * @return Mage_Rss_Controller_AdminhtmlAbstract
     */
    public function preDispatch()
    {
        $action = $this->getRequest()->getActionName();
        $map = $this->_getAdminAclMap();
        if (isset($map[$action])) {
            $this->_currentArea = 'adminhtml';
            $path = $map[$action];
            Mage::helper('Mage_Rss_Helper_Data')->authAdmin($path);
        }
        return parent::preDispatch();
    }
}
