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
 * Theme controller
 */
class Mage_Theme_Adminhtml_System_Design_ThemeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Theme service model
     *
     * @var Mage_Theme_Model_Uploader_Service
     */
    protected $_serviceModel;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param string $areaCode
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     * @param Mage_Theme_Model_Uploader_Service $service
     * @param array $invokeArgs
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Response_Http $response,
        $areaCode = null,
        Magento_ObjectManager $objectManager,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Layout_Factory $layoutFactory,
        Mage_Theme_Model_Uploader_Service $service,
        array $invokeArgs = array()
    ) {
        $this->_serviceModel = $service;

        parent::__construct($request, $response, $areaCode, $objectManager, $frontController, $layoutFactory,
            $invokeArgs
        );
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_objectManager->get('Mage_Core_Model_Event_Manager')->dispatch('theme_registration_from_filesystem');
        $this->loadLayout();
        $this->_setActiveMenu('Mage_Theme::system_design_theme');
        $this->renderLayout();
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new theme
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit theme
     */
    public function editAction()
    {
        $themeId = (int) $this->getRequest()->getParam('id');
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->_objectManager->create('Mage_Core_Model_Theme');
        try {
            if ($themeId && !$theme->load($themeId)->getId()) {
                Mage::throwException($this->__('Theme was not found.'));
            }
            Mage::register('current_theme', $theme);

            $this->loadLayout();

            /** @var $tab Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css */
            $tab = $this->getLayout()->getBlock('theme_edit_tabs_tab_css_tab');
            if ($tab && $tab->canShowTab()) {
                /** @var $helper Mage_Theme_Helper_Data */
                $helper = $this->_objectManager->get('Mage_Theme_Helper_Data');

                $files = $helper->getCssFiles($theme);
                $tab->setFiles($files);
            }
            $this->_setActiveMenu('Mage_Adminhtml::system_design_theme');
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('The theme was not found.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->_objectManager->create('Mage_Core_Model_Theme');
        /** @var $themeCss Mage_Core_Model_Theme_Files_Css */
        $themeCss = $this->_objectManager->create('Mage_Core_Model_Theme_Files_Css');
        try {
            if ($this->getRequest()->getPost()) {
                $themeData = $this->getRequest()->getParam('theme');
                $customCssData = $this->getRequest()->getParam('custom_css_content');

                $theme->saveFormData($themeData);
                $themeCss->saveFormData($theme, $customCssData);

                $this->_getSession()->addSuccess($this->__('The theme has been saved.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setThemeData($themeData);
            $this->_getSession()->setThemeCustomCssData($customCssData);
            $redirectBack = true;
        } catch (Exception $e) {
            $this->_getSession()->addError('The theme was not saved');
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $redirectBack ? $this->_redirect('*/*/edit', array('id' => $theme->getId())) : $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        $themeId = $this->getRequest()->getParam('id');
        try {
            if ($themeId) {
                /** @var $theme Mage_Core_Model_Theme */
                $theme = $this->_objectManager->create('Mage_Core_Model_Theme')->load($themeId);
                if (!$theme->getId()) {
                    throw new InvalidArgumentException($this->__('Theme with id "%d" is not found.', $themeId));
                }
                if (!$theme->isVirtual()) {
                    throw new InvalidArgumentException(
                        $this->__('Only virtual theme is possible to delete.', $themeId)
                    );
                }
                $theme->delete();
                $this->_getSession()->addSuccess($this->__('The theme has been deleted.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot delete the theme.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        /**
         * @todo Temporary solution. Theme module should not know about the existence of editor module.
         */
        $redirectBack ? $this->_redirect('*/system_design_editor/index/') : $this->_redirect('*/*/');
    }

    /**
     * Upload css file
     */
    public function uploadCssAction()
    {
        $serviceModel = $this->_serviceModel;
        try {
            $cssFileContent = $serviceModel->uploadCssFile('css_file_uploader')->getFileContent();
            $result = array('error' => false, 'content' => $cssFileContent);
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $this->__('Cannot upload css file'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Download css file
     */
    public function downloadCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $file = $this->getRequest()->getParam('file');

        /** @var $helper Mage_Theme_Helper_Data */
        $helper = $this->_objectManager->get('Mage_Theme_Helper_Data');
        $fileName = $helper->urlDecode($file);
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->_objectManager->create('Mage_Core_Model_Theme')->load($themeId);
            if (!$theme->getId()) {
                throw new InvalidArgumentException($this->__('Theme with id "%d" is not found.', $themeId));
            }

            $themeCss = $helper->getCssFiles($theme);
            if (!isset($themeCss[$fileName])) {
                throw new InvalidArgumentException(
                    $this->__('Css file "%s" is not in the theme with id "%d".', $fileName, $themeId)
                );
            }

            $this->_prepareDownloadResponse($fileName, array(
                'type'  => 'filename',
                'value' => $themeCss[$fileName]
            ));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('File "%s" is not found.', $fileName));
            $this->_redirectUrl($this->_getRefererUrl());
        }
    }

    /**
     * Check the permission to manage themes
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_objectManager->get('Mage_Core_Model_Authorization')->isAllowed('Mage_Theme::theme');
    }
}
