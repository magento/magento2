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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_System_BackupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Backup list action
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('Backups'));

        if($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('system');
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('System'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('System'));
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Tools'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Tools'));
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Backups'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Backup'));

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Backup', 'backup'));

        $this->renderLayout();
    }

    /**
     * Backup list action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('Mage_Adminhtml_Block_Backup_Grid')->toHtml());
    }

    /**
     * Create backup action
     */
    public function createAction()
    {
        try {
            $backupDb = Mage::getModel('Mage_Backup_Model_Db');
            $backup   = Mage::getModel('Mage_Backup_Model_Backup')
                ->setTime(time())
                ->setType('db')
                ->setPath(Mage::getBaseDir("var") . DS . "backups");

            Mage::register('backup_model', $backup);

            $backupDb->createBackup($backup);
            $this->_getSession()->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('The backup has been created.'));
        }
        catch (Exception  $e) {
            $this->_getSession()->addException($e, Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while creating the backup.'));
        }
        $this->_redirect('*/*');
    }

    /**
     * Download backup action
     */
    public function downloadAction()
    {
        $backup = Mage::getModel('Mage_Backup_Model_Backup')
            ->setTime((int)$this->getRequest()->getParam('time'))
            ->setType($this->getRequest()->getParam('type'))
            ->setPath(Mage::getBaseDir("var") . DS . "backups");
        /* @var $backup Mage_Backup_Model_Backup */

        if (!$backup->exists()) {
            $this->_redirect('*/*');
        }

        $fileName = 'backup-' . date('YmdHis', $backup->getTime()) . '.sql.gz';

        $this->_prepareDownloadResponse($fileName, null, 'application/octet-stream', $backup->getSize());

        $this->getResponse()->sendHeaders();

        $backup->output();
        exit();
    }

    /**
     * Delete backup action
     */
    public function deleteAction()
    {
        try {
            $backup = Mage::getModel('Mage_Backup_Model_Backup')
                ->setTime((int)$this->getRequest()->getParam('time'))
                ->setType($this->getRequest()->getParam('type'))
                ->setPath(Mage::getBaseDir("var") . DS . "backups")
                ->deleteFile();

            Mage::register('backup_model', $backup);

            $this->_getSession()->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Backup record was deleted.'));
        }
        catch (Exception $e) {
                // Nothing
        }

        $this->_redirect('*/*/');

    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('system/tools/backup');
    }

    /**
     * Retrive adminhtml session model
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session');
    }
}
