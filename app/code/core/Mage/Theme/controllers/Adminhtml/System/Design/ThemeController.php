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
        $this->loadLayout(false);
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
            /** @var $cssFileModel Mage_Core_Model_Theme_Customization_Files_Css */
            $cssFileModel = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css');
            /** @var $jsFileModel Mage_Core_Model_Theme_Customization_Files_Js */
            $jsFileModel = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');
            $theme->setCustomization($cssFileModel)->setCustomization($jsFileModel);

            $jsFileModel->removeTemporaryFiles($theme);
            Mage::register('current_theme', $theme);

            $this->loadLayout();
            /** @var $tab Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css */
            $tab = $this->getLayout()->getBlock('theme_edit_tabs_tab_css_tab');
            if ($tab && $tab->canShowTab()) {
                /** @var $helper Mage_Core_Helper_Theme */
                $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
                $files = $helper->getGroupedCssFiles($theme);
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
        $themeData = $this->getRequest()->getParam('theme');
        $customCssData = $this->getRequest()->getParam('custom_css_content');
        $uploadJsFiles = (array)$this->getRequest()->getParam('js_uploaded_files');
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        $reorderJsFiles = array_keys($this->getRequest()->getParam('js_order', array()));

        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->_objectManager->create('Mage_Core_Model_Theme');
        /** @var $themeCss Mage_Core_Model_Theme_Customization_Files_Css */
        $themeCss = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css');
        /** @var $themeJs Mage_Core_Model_Theme_Customization_Files_Js */
        $themeJs = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');

        try {
            if ($this->getRequest()->getPost()) {
                $themeCss->setDataForSave($customCssData);
                $theme->setCustomization($themeCss);

                $themeJs->setDataForSave($uploadJsFiles);
                $themeJs->setDataForDelete($removeJsFiles);
                $themeJs->setJsOrderData($reorderJsFiles);
                $theme->setCustomization($themeJs);

                $theme->saveFormData($themeData);
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
                    throw new InvalidArgumentException(sprintf('Theme with id "%d" is not found.', $themeId));
                }
                if (!$theme->isVirtual()) {
                    throw new InvalidArgumentException(
                        sprintf('Only virtual theme is possible to delete and theme "%s" isn\'t virtual', $themeId)
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
        /** @var $serviceModel Mage_Theme_Model_Uploader_Service */
        $serviceModel = $this->_objectManager->get('Mage_Theme_Model_Uploader_Service');
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
     * Upload js file
     *
     * @throws Mage_Core_Exception
     */
    public function uploadJsAction()
    {
        /** @var $serviceModel Mage_Theme_Model_Uploader_Service */
        $serviceModel = $this->_objectManager->get('Mage_Theme_Model_Uploader_Service');
        $themeId = $this->getRequest()->getParam('id');
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->_objectManager->create('Mage_Core_Model_Theme')->load($themeId);
            if (!$theme->getId()) {
                Mage::throwException($this->__('Theme with id "%d" is not found.', $themeId));
            }
            $serviceModel->uploadJsFile('js_files_uploader', $theme);

            $this->loadLayout();

            /** @var $filesJs Mage_Core_Model_Theme_Customization_Files_Js */
            $filesJs = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');
            $customJsFiles = $theme->setCustomization($filesJs)
                ->getCustomizationData(Mage_Core_Model_Theme_Customization_Files_Js::TYPE);

            $jsItemsBlock = $this->getLayout()->getBlock('theme_js_file_list');
            $jsItemsBlock->setJsFiles($customJsFiles);
            $result = array('content' => $jsItemsBlock->toHtml());
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $this->__('Cannot upload js file'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Download custom css file
     */
    public function downloadCustomCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->_objectManager->create('Mage_Core_Model_Theme')->load($themeId);
            if (!$theme->getId()) {
                throw new InvalidArgumentException('Theme with id ' . $themeId . ' is not found.');
            }

            /** @var $filesCss Mage_Core_Model_Theme_Customization_Files_Css */
            $filesCss = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css');
            /** @var $customCssFile Mage_Core_Model_Theme_Files */
            $customCssFile = $theme->setCustomization($filesCss)
                ->getCustomizationData(Mage_Core_Model_Theme_Customization_Files_Css::TYPE)->getFirstItem();

            if ($customCssFile->getContent()) {
                $this->_prepareDownloadResponse(Mage_Core_Model_Theme_Customization_Files_Css::FILE_PATH, array(
                    'type'  => 'filename',
                    'value' => $customCssFile->getFullPath()
                ));
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                $this->__('File "%s" is not found.', Mage_Core_Model_Theme_Customization_Files_Css::FILE_PATH));
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
    }

    /**
     * Download css file
     */
    public function downloadCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $file = $this->getRequest()->getParam('file');

        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        $fileName = $helper->urlDecode($file);
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->_objectManager->create('Mage_Core_Model_Theme')->load($themeId);
            if (!$theme->getId()) {
                throw new InvalidArgumentException(sprintf('Theme with id "%d" is not found.', $themeId));
            }

            $themeCss = $helper->getCssFiles($theme);
            if (!isset($themeCss[$fileName])) {
                throw new InvalidArgumentException(
                    sprintf('Css file "%s" is not in the theme with id "%d".', $fileName, $themeId)
                );
            }

            $this->_prepareDownloadResponse($fileName, array(
                'type'  => 'filename',
                'value' => $themeCss[$fileName]['path']
            ));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('File "%s" is not found.', $fileName));
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
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
