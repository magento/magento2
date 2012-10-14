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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert Advanced admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_System_Convert_ProfileController extends Mage_Adminhtml_Controller_Action
{
    protected function _initProfile($idFieldName = 'id')
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Import and Export'))
             ->_title($this->__('Profiles'));

        $profileId = (int) $this->getRequest()->getParam($idFieldName);
        $profile = Mage::getModel('Mage_Dataflow_Model_Profile');

        if ($profileId) {
            $profile->load($profileId);
            if (!$profile->getId()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    $this->__('The profile you are trying to save no longer exists'));
                $this->_redirect('*/*');
                return false;
            }
        }

        Mage::register('current_convert_profile', $profile);

        return $this;
    }

    /**
     * Profiles list action
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Import and Export'))
             ->_title($this->__('Advanced Profiles'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Mage_Adminhtml::system_convert');

        /**
         * Append profiles block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Convert_Profile', 'convert_profile')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Import/Export'),
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Import/Export Advanced'));
        $this->_addBreadcrumb(
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Advanced Profiles'),
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Advanced Profiles'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Convert_Profile_Grid')->toHtml()
        );
    }

    /**
     * Profile edit action
     */
    public function editAction()
    {
        $this->_initProfile();
        $this->loadLayout();

        $profile = Mage::registry('current_convert_profile');

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getConvertProfileData(true);

        if (!empty($data)) {
            $profile->addData($data);
        }

        $this->_title($profile->getId() ? $profile->getName() : $this->__('New Profile'));

        $this->_setActiveMenu('Mage_Adminhtml::system_convert');

        $this->_addContent(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Convert_Profile_Edit')
        );

        /**
         * Append edit tabs to left block
         */
        $this->_addLeft($this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tabs'));

        $this->renderLayout();
    }

    /**
     * Create new profile action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Delete profile action
     */
    public function deleteAction()
    {
        $this->_initProfile();
        $profile = Mage::registry('current_convert_profile');
        if ($profile->getId()) {
            try {
                $profile->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('The profile has been deleted.'));
            } catch (Exception $e){
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }

    /**
     * Save profile action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (!$this->_initProfile('profile_id')) {
                return;
            }
            $profile = Mage::registry('current_convert_profile');

            // Prepare profile saving data
            if (isset($data)) {
                $profile->addData($data);
            }

            try {
                $profile->save();

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('The profile has been saved.'));
            } catch (Exception $e){
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setConvertProfileData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id' => $profile->getId())));
                return;
            }
            if ($this->getRequest()->getParam('continue')) {
                $this->_redirect('*/*/edit', array('id' => $profile->getId()));
            } else {
                $this->_redirect('*/*');
            }
        } else {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                $this->__('Invalid POST data (please check post_max_size and upload_max_filesize settings in your php.ini file).')
            );
            $this->_redirect('*/*');
        }
    }

    public function runAction()
    {
        $this->_initProfile();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function batchRunAction()
    {
        if ($this->getRequest()->isPost()) {
            $batchId = $this->getRequest()->getPost('batch_id', 0);
            $rowIds  = $this->getRequest()->getPost('rows');

            /* @var $batchModel Mage_Dataflow_Model_Batch */
            $batchModel = Mage::getModel('Mage_Dataflow_Model_Batch')->load($batchId);

            if (!$batchModel->getId()) {
                return;
            }
            if (!is_array($rowIds) || count($rowIds) < 1) {
                return;
            }
            if (!$batchModel->getAdapter()) {
                return;
            }

            $batchImportModel = $batchModel->getBatchImportModel();
            $importIds = $batchImportModel->getIdCollection();

            $adapter = Mage::getModel($batchModel->getAdapter());
            $adapter->setBatchParams($batchModel->getParams());

            $errors = array();
            $saved  = 0;
            foreach ($rowIds as $importId) {
                $batchImportModel->load($importId);
                if (!$batchImportModel->getId()) {
                    $errors[] = Mage::helper('Mage_Dataflow_Helper_Data')->__('Skip undefined row.');
                    continue;
                }

                try {
                    $importData = $batchImportModel->getBatchData();
                    $adapter->saveRow($importData);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    continue;
                }
                $saved ++;
            }

            if (method_exists($adapter, 'getEventPrefix')) {
                /**
                 * Event for process rules relations after products import
                 */
                Mage::dispatchEvent($adapter->getEventPrefix() . '_finish_before', array(
                    'adapter' => $adapter
                ));

                /**
                 * Clear affected ids for adapter possible reuse
                 */
                $adapter->clearAffectedEntityIds();
            }

            $result = array(
                'savedRows' => $saved,
                'errors'    => $errors
            );
            $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result));
        }
    }

    public function batchFinishAction()
    {
        $batchId = $this->getRequest()->getParam('id');
        if ($batchId) {
            $batchModel = Mage::getModel('Mage_Dataflow_Model_Batch')->load($batchId);
            /* @var $batchModel Mage_Dataflow_Model_Batch */

            if ($batchModel->getId()) {
                $result = array();
                try {
                    $batchModel->beforeFinish();
                } catch (Mage_Core_Exception $e) {
                    $result['error'] = $e->getMessage();
                } catch (Exception $e) {
                    $result['error'] = Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while finishing process. Please refresh the cache');
                }
                $batchModel->delete();
                $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result));
            }
        }
    }

    /**
     * Customer orders grid
     *
     */
    public function historyAction() {
        $this->_initProfile();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_History')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Adminhtml::profiles');
    }
}
