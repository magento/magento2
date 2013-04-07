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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Files controller
 */
class Mage_Theme_Adminhtml_System_Design_Wysiwyg_FilesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->loadLayout('overlay_popup');
        $this->renderLayout();
    }

    /**
     * Tree json action
     */
    public function treeJsonAction()
    {
        try {
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Tree')
                    ->getTreeJson($this->_getStorage()->getTreeArray())
            );
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode(array()));
        }
    }

    /**
     * New folder action
     */
    public function newFolderAction()
    {
        $name = $this->getRequest()->getPost('name');
        try {
            $path = $this->_getSession()->getStoragePath();
            $result = $this->_getStorage()->createFolder($name, $path);
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $this->__('Unknown error'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $this->_redirect('*/*/');
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Delete folder action
     */
    public function deleteFolderAction()
    {
        try {
            $path = $this->_getSession()->getStoragePath();
            $this->_getStorage()->deleteDirectory($path);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
        }
    }

    /**
     * Contents action
     */
    public function contentsAction()
    {
        try {
            $this->loadLayout('empty');
            $this->getLayout()->getBlock('wysiwyg_files.files')->setStorage($this->_getStorage());
            $this->renderLayout();

            $this->_getSession()->setStoragePath(
                $this->_objectManager->get('Mage_Theme_Helper_Storage')->getCurrentPath()
            );
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
        }
    }

    /**
     * Files upload action
     */
    public function uploadAction()
    {
        try {
            $path = $this->_getSession()->getStoragePath();
            $result = $this->_getStorage()->uploadFile($path);
        } catch (Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Preview image action
     */
    public function previewImageAction()
    {
        $file = $this->getRequest()->getParam('file');
        /** @var $helper Mage_Theme_Helper_Storage */
        $helper = $this->_objectManager->get('Mage_Theme_Helper_Storage');
        try {
            $this->_prepareDownloadResponse($file, array(
                'type'  => 'filename',
                'value' => $helper->getThumbnailPath($file)
            ));
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $this->_redirect('core/index/notfound');
        }
    }

    /**
     * Delete file from media storage
     * @throws Exception
     */
    public function deleteFilesAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new Exception('Wrong request');
            }
            $files = $this->_objectManager->get('Mage_Core_Helper_Data')->jsonDecode(
                $this->getRequest()->getParam('files')
            );
            foreach ($files as $file) {
                $this->_getStorage()->deleteFile($file);
            }
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
        }
    }

    /**
     * Fire when select image
     */
    public function onInsertAction()
    {
        /** @var $helperStorage Mage_Theme_Helper_Storage */
        $helperStorage = $this->_objectManager->get('Mage_Theme_Helper_Storage');
        $this->getResponse()->setBody($helperStorage->getRelativeUrl());
    }

    /**
     * Get storage
     *
     * @return Mage_Theme_Model_Wysiwyg_Storage
     */
    protected function _getStorage()
    {
        return $this->_objectManager->get('Mage_Theme_Model_Wysiwyg_Storage');
    }
}
