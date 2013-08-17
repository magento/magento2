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
 * Backend controller for the design editor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_DesignEditor_Adminhtml_System_Design_Editor_ToolsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize theme context model
     *
     * @return Mage_DesignEditor_Model_Theme_Context
     */
    protected function _initContext()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        /** @var Mage_DesignEditor_Model_Theme_Context $themeContext */
        $themeContext = $this->_objectManager->get('Mage_DesignEditor_Model_Theme_Context');
        return $themeContext->setEditableThemeById($themeId);
    }

    /**
     *  Upload custom CSS action
     */
    public function uploadAction()
    {
        /** @var $cssService Mage_Theme_Model_Theme_Customization_File_CustomCss */
        $cssService = $this->_objectManager->get('Mage_Theme_Model_Theme_Customization_File_CustomCss');
        /** @var $singleFile Mage_Theme_Model_Theme_SingleFile */
        $singleFile = $this->_objectManager->create('Mage_Theme_Model_Theme_SingleFile',
            array('fileService' => $cssService));
        /** @var $serviceModel Mage_Theme_Model_Uploader_Service */
        $serviceModel = $this->_objectManager->get('Mage_Theme_Model_Uploader_Service');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $cssFileData = $serviceModel->uploadCssFile(
                Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Custom::FILE_ELEMENT_NAME
            );
            $singleFile->update($editableTheme, $cssFileData['content']);
            $response = array(
                'success' => true,
                'message' => $this->__('You updated the custom.css file.'),
                'content' => $cssFileData['content']
            );
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $this->__('We cannot upload the CSS file.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Save custom css file
     */
    public function saveCssContentAction()
    {
        $customCssContent = (string)$this->getRequest()->getParam('custom_css_content', '');
        /** @var $cssService Mage_Theme_Model_Theme_Customization_File_CustomCss */
        $cssService = $this->_objectManager->get('Mage_Theme_Model_Theme_Customization_File_CustomCss');
        /** @var $singleFile Mage_Theme_Model_Theme_SingleFile */
        $singleFile = $this->_objectManager->create('Mage_Theme_Model_Theme_SingleFile',
            array('fileService' => $cssService));
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $customCss = $singleFile->update($editableTheme, $customCssContent);
            $response = array(
                'success'  => true,
                'filename' => $customCss->getFileName(),
                'message'  => $this->__('You updated the %s file.', $customCss->getFileName())
            );
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $this->__('We can\'t save the custom css file.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Ajax list of existing javascript files
     */
    public function jsListAction()
    {
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $customization = $editableTheme->getCustomization();
            $customJsFiles = $customization->getFilesByType(Mage_Core_Model_Theme_Customization_File_Js::TYPE);
            $result = array('error' => false, 'files' => $customization->generateFileInfo($customJsFiles));
            $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
    }

    /**
     * Upload js file
     */
    public function uploadJsAction()
    {
        /** @var $serviceModel Mage_Theme_Model_Uploader_Service */
        $serviceModel = $this->_objectManager->get('Mage_Theme_Model_Uploader_Service');
        /** @var $jsService Mage_Core_Model_Theme_Customization_File_Js */
        $jsService = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_File_Js');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($editableTheme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();
            $this->_forward('jsList');
            return;
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $this->__('We cannot upload the JS file.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Delete custom file action
     */
    public function deleteCustomFilesAction()
    {
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $editableTheme->getCustomization()->delete($removeJsFiles);
            $this->_forward('jsList');
        } catch (Exception $e) {
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
    }

    /**
     * Reorder js file
     */
    public function reorderJsAction()
    {
        $reorderJsFiles = (array)$this->getRequest()->getParam('js_order', array());
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $editableTheme->getCustomization()->reorder(
                Mage_Core_Model_Theme_Customization_File_Js::TYPE, $reorderJsFiles
            );
            $result = array('success' => true);
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $this->__('We cannot upload the CSS file.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Save image sizes
     */
    public function saveImageSizingAction()
    {
        $imageSizing = $this->getRequest()->getParam('imagesizing');
        /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
        $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');
        /** @var $imageSizingValidator Mage_DesignEditor_Model_Editor_Tools_ImageSizing_Validator */
        $imageSizingValidator = $this->_objectManager->get(
            'Mage_DesignEditor_Model_Editor_Tools_ImageSizing_Validator'
        );
        try {
            $themeContext = $this->_initContext();
            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_IMAGE_SIZING,
                $themeContext->getStagingTheme(),
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $imageSizing = $imageSizingValidator->validate($configuration->getAllControlsData(), $imageSizing);
            $configuration->saveData($imageSizing);
            $result = array('success' => true, 'message' => $this->__('We saved the image sizes.'));
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $this->__('We can\'t save image sizes.'));
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));

    }

    /**
     * Upload quick style image
     */
    public function uploadQuickStyleImageAction()
    {
        /** @var $uploaderModel Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader */
        $uploaderModel = $this->_objectManager->get('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader');
        try {
            /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
            $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $keys = array_keys($this->getRequest()->getFiles());
            $result = $uploaderModel->setTheme($editableTheme)->uploadFile($keys[0]);

            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES,
                $editableTheme,
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData(array($keys[0] => $result['css_path']));

            $response = array('error' => false, 'content' => $result);
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $errorMessage = $this->__('Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Remove quick style image
     */
    public function removeQuickStyleImageAction()
    {
        $fileName = $this->getRequest()->getParam('file_name', false);
        $elementName = $this->getRequest()->getParam('element', false);

        /** @var $uploaderModel Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader */
        $uploaderModel = $this->_objectManager->get('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $result = $uploaderModel->setTheme($editableTheme)->removeFile($fileName);

            /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
            $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');

            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES,
                $editableTheme,
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData(array($elementName => ''));

            $response = array('error' => false, 'content' => $result);
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $errorMessage = $this->__('Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Upload store logo
     *
     * @throws Mage_Core_Exception
     */
    public function uploadStoreLogoAction()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->_objectManager->create('Mage_Core_Model_Theme');
            if (!$theme->load($themeId)->getId() || !$theme->isEditable()) {
                throw new Mage_Core_Exception(
                    $this->__('The file can\'t be found or edited.')
                );
            }

            /** @var $customizationConfig Mage_Theme_Model_Config_Customization */
            $customizationConfig = $this->_objectManager->get('Mage_Theme_Model_Config_Customization');
            $store = $this->_objectManager->get('Mage_Core_Model_Store')->load($storeId);

            if (!$customizationConfig->isThemeAssignedToStore($theme, $store)) {
                throw new Mage_Core_Exception($this->__('This theme is not assigned to a store view.',
                    $theme->getId()));
            }
            /** @var $storeLogo Mage_DesignEditor_Model_Editor_Tools_QuickStyles_LogoUploader */
            $storeLogo = $this->_objectManager->get('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_LogoUploader');
            $storeLogo->setScope('stores')->setScopeId($store->getId())->setPath('design/header/logo_src')->save();

            $this->_reinitSystemConfiguration();

            $response = array('error' => false, 'content' => array('name' => basename($storeLogo->getValue())));
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $errorMessage = $this->__('Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Remove store logo
     *
     * @throws Mage_Core_Exception
     */
    public function removeStoreLogoAction()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->_objectManager->create('Mage_Core_Model_Theme');
            if (!$theme->load($themeId)->getId() || !$theme->isEditable()) {
                throw new Mage_Core_Exception(
                    $this->__('The file can\'t be found or edited.')
                );
            }

            /** @var $customizationConfig Mage_Theme_Model_Config_Customization */
            $customizationConfig = $this->_objectManager->get('Mage_Theme_Model_Config_Customization');
            $store = $this->_objectManager->get('Mage_Core_Model_Store')->load($storeId);

            if (!$customizationConfig->isThemeAssignedToStore($theme, $store)) {
                throw new Mage_Core_Exception($this->__('This theme is not assigned to a store view.',
                    $theme->getId()));
            }

            $this->_objectManager->get('Mage_Backend_Model_Config_Backend_Store')
                ->setScope('stores')->setScopeId($store->getId())->setPath('design/header/logo_src')
                ->setValue('')->save();

            $this->_reinitSystemConfiguration();
            $response = array('error' => false, 'content' => array());
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $errorMessage = $this->__('Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Save quick styles data
     */
    public function saveQuickStylesAction()
    {
        $controlId = $this->getRequest()->getParam('id');
        $controlValue = $this->getRequest()->getParam('value');
        try {
            $themeContext = $this->_initContext();
            /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
            $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');
            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES,
                $themeContext->getStagingTheme(),
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData(array($controlId => $controlValue));
            $response = array('success' => true);
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        } catch (Exception $e) {
            $errorMessage = $this->__('Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Re-init system configuration
     *
     * @return Mage_Core_Model_Config
     */
    protected function _reinitSystemConfiguration()
    {
        /** @var $configModel Mage_Core_Model_Config */
        $configModel = $this->_objectManager->get('Mage_Core_Model_Config');
        return $configModel->reinit();
    }
}
