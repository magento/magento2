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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend controller for the design editor
 */
class Mage_DesignEditor_Adminhtml_System_Design_EditorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Display the design editor launcher page
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Design'))->_title($this->__('Editor'));
        $this->loadLayout();
        $this->_setActiveMenu('system/design/editor');
        $this->renderLayout();
    }

    /**
     * Activate the design editor in the session and redirect to the frontend of the selected store
     */
    public function launchAction()
    {
        /** @var $session Mage_DesignEditor_Model_Session */
        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $session->activateDesignEditor();
        /* Redirect to the frontend */
        $query = Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM . '=' . urlencode($session->getSessionId());
        if (!Mage::app()->isSingleStoreMode()) {
            $storeId = (int)$this->getRequest()->getParam('store_id');
            $store = Mage::app()->getStore($storeId);
            $baseUrl = $store->getBaseUrl();
            $query .= '&___store=' . urlencode($store->getCode());
        } else {
            $baseUrl = Mage::app()->getStore(true)->getBaseUrl();
        }
        $this->_redirectUrl($baseUrl . '?' . $query);
    }

    /**
     * Exit design editor
     */
    public function exitAction()
    {
        /** @var $session Mage_DesignEditor_Model_Session */
        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $session->deactivateDesignEditor();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Whether the current user has enough permissions to execute an action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('system/design/editor');
    }
}
