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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

use Magento\Framework\Model\Exception as CoreException;

/**
 * Backend controller for the design editor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tools extends \Magento\Backend\App\Action
{
    /**
     * Initialize theme context model
     *
     * @return \Magento\DesignEditor\Model\Theme\Context
     */
    protected function _initContext()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        /** @var \Magento\DesignEditor\Model\Theme\Context $themeContext */
        $themeContext = $this->_objectManager->get('Magento\DesignEditor\Model\Theme\Context');
        return $themeContext->setEditableThemeById($themeId);
    }

    /**
     * Upload custom CSS action
     *
     * @return void
     */
    public function uploadAction()
    {
        /** @var $cssService \Magento\Theme\Model\Theme\Customization\File\CustomCss */
        $cssService = $this->_objectManager->get('Magento\Theme\Model\Theme\Customization\File\CustomCss');
        /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
        $singleFile = $this->_objectManager->create(
            'Magento\Theme\Model\Theme\SingleFile',
            array('fileService' => $cssService)
        );
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $cssFileData = $serviceModel->uploadCssFile(
                \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Custom::FILE_ELEMENT_NAME
            );
            $singleFile->update($editableTheme, $cssFileData['content']);
            $response = array(
                'success' => true,
                'message' => __('You updated the custom.css file.'),
                'content' => $cssFileData['content']
            );
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('We cannot upload the CSS file.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Save custom css file
     *
     * @return void
     */
    public function saveCssContentAction()
    {
        $customCssContent = (string)$this->getRequest()->getParam('custom_css_content', '');
        /** @var $cssService \Magento\Theme\Model\Theme\Customization\File\CustomCss */
        $cssService = $this->_objectManager->get('Magento\Theme\Model\Theme\Customization\File\CustomCss');
        /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
        $singleFile = $this->_objectManager->create(
            'Magento\Theme\Model\Theme\SingleFile',
            array('fileService' => $cssService)
        );
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $customCss = $singleFile->update($editableTheme, $customCssContent);
            $response = array(
                'success' => true,
                'filename' => $customCss->getFileName(),
                'message' => __('You updated the %1 file.', $customCss->getFileName())
            );
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('We can\'t save the custom css file.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Ajax list of existing javascript files
     *
     * @return void
     */
    public function jsListAction()
    {
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $customization = $editableTheme->getCustomization();
            $customJsFiles = $customization->getFilesByType(\Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE);
            $result = array('error' => false, 'files' => $customization->generateFileInfo($customJsFiles));
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }

    /**
     * Upload js file
     *
     * @return void
     */
    public function uploadJsAction()
    {
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        /** @var $jsService \Magento\Framework\View\Design\Theme\Customization\File\Js */
        $jsService = $this->_objectManager->create('Magento\Framework\View\Design\Theme\Customization\File\Js');
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
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('We cannot upload the JS file.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Delete custom file action
     *
     * @return void
     */
    public function deleteCustomFilesAction()
    {
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $editableTheme->getCustomization()->delete($removeJsFiles);
            $this->_forward('jsList');
        } catch (\Exception $e) {
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }

    /**
     * Reorder js file
     *
     * @return void
     */
    public function reorderJsAction()
    {
        $reorderJsFiles = (array)$this->getRequest()->getParam('js_order', array());
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $editableTheme->getCustomization()->reorder(
                \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE,
                $reorderJsFiles
            );
            $result = array('success' => true);
        } catch (CoreException $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('We cannot upload the CSS file.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Save image sizes
     *
     * @return void
     */
    public function saveImageSizingAction()
    {
        $imageSizing = $this->getRequest()->getParam('imagesizing');
        /** @var $configFactory \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory */
        $configFactory = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\Controls\Factory');
        /** @var $imageSizingValidator \Magento\DesignEditor\Model\Editor\Tools\ImageSizing\Validator */
        $imageSizingValidator = $this->_objectManager->get(
            'Magento\DesignEditor\Model\Editor\Tools\ImageSizing\Validator'
        );
        try {
            $themeContext = $this->_initContext();
            $configuration = $configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_IMAGE_SIZING,
                $themeContext->getStagingTheme(),
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $imageSizing = $imageSizingValidator->validate($configuration->getAllControlsData(), $imageSizing);
            $configuration->saveData($imageSizing);
            $result = array('success' => true, 'message' => __('We saved the image sizes.'));
        } catch (CoreException $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('We can\'t save image sizes.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Upload quick style image
     *
     * @return void
     */
    public function uploadQuickStyleImageAction()
    {
        /** @var $uploaderModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader */
        $uploaderModel = $this->_objectManager->get(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader'
        );
        try {
            /** @var $configFactory \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory */
            $configFactory = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\Controls\Factory');
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $keys = array_keys($this->getRequest()->getFiles());
            $result = $uploaderModel->setTheme($editableTheme)->uploadFile($keys[0]);

            $configuration = $configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_QUICK_STYLES,
                $editableTheme,
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData(array($keys[0] => $result['css_path']));

            $response = array('error' => false, 'content' => $result);
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $errorMessage = __(
                'Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).'
            );
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Remove quick style image
     *
     * @return void
     */
    public function removeQuickStyleImageAction()
    {
        $fileName = $this->getRequest()->getParam('file_name', false);
        $elementName = $this->getRequest()->getParam('element', false);

        /** @var $uploaderModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader */
        $uploaderModel = $this->_objectManager->get(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader'
        );
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $result = $uploaderModel->setTheme($editableTheme)->removeFile($fileName);

            /** @var $configFactory \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory */
            $configFactory = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\Controls\Factory');

            $configuration = $configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_QUICK_STYLES,
                $editableTheme,
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData(array($elementName => ''));

            $response = array('error' => false, 'content' => $result);
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $errorMessage = __(
                'Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).'
            );
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Upload store logo
     *
     * @return void
     * @throws CoreException
     */
    public function uploadStoreLogoAction()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        try {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
            if (!$theme->load($themeId)->getId() || !$theme->isEditable()) {
                throw new CoreException(__('The file can\'t be found or edited.'));
            }

            /** @var $customizationConfig \Magento\Theme\Model\Config\Customization */
            $customizationConfig = $this->_objectManager->get('Magento\Theme\Model\Config\Customization');
            $store = $this->_objectManager->get('Magento\Store\Model\Store')->load($storeId);

            if (!$customizationConfig->isThemeAssignedToStore($theme, $store)) {
                throw new CoreException(__('This theme is not assigned to a store view #%1.', $theme->getId()));
            }
            /** @var $storeLogo \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\LogoUploader */
            $storeLogo = $this->_objectManager->get(
                'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\LogoUploader'
            );
            $storeLogo->setScope('stores')->setScopeId($store->getId())->setPath('design/header/logo_src')->save();

            $this->_reinitSystemConfiguration();

            $response = array('error' => false, 'content' => array('name' => basename($storeLogo->getValue())));
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $errorMessage = __(
                'Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).'
            );
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Remove store logo
     *
     * @return void
     * @throws CoreException
     */
    public function removeStoreLogoAction()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        try {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
            if (!$theme->load($themeId)->getId() || !$theme->isEditable()) {
                throw new CoreException(__('The file can\'t be found or edited.'));
            }

            /** @var $customizationConfig \Magento\Theme\Model\Config\Customization */
            $customizationConfig = $this->_objectManager->get('Magento\Theme\Model\Config\Customization');
            $store = $this->_objectManager->get('Magento\Store\Model\Store')->load($storeId);

            if (!$customizationConfig->isThemeAssignedToStore($theme, $store)) {
                throw new CoreException(__('This theme is not assigned to a store view #%1.', $theme->getId()));
            }

            $this->_objectManager->get(
                'Magento\Backend\Model\Config\Backend\Store'
            )->setScope(
                'stores'
            )->setScopeId(
                $store->getId()
            )->setPath(
                'design/header/logo_src'
            )->setValue(
                ''
            )->save();

            $this->_reinitSystemConfiguration();
            $response = array('error' => false, 'content' => array());
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $errorMessage = __(
                'Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).'
            );
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Save quick styles data
     *
     * @return void
     */
    public function saveQuickStylesAction()
    {
        $controlId = $this->getRequest()->getParam('id');
        $controlValue = $this->getRequest()->getParam('value');
        try {
            $themeContext = $this->_initContext();
            /** @var $configFactory \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory */
            $configFactory = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\Controls\Factory');
            $configuration = $configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_QUICK_STYLES,
                $themeContext->getStagingTheme(),
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData(array($controlId => $controlValue));
            $response = array('success' => true);
        } catch (CoreException $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $errorMessage = __(
                'Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).'
            );
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }

    /**
     * Re-init system configuration
     *
     * @return \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected function _reinitSystemConfiguration()
    {
        /** @var $configModel \Magento\Framework\App\Config\ReinitableConfigInterface */
        $configModel = $this->_objectManager->get('Magento\Framework\App\Config\ReinitableConfigInterface');
        return $configModel->reinit();
    }
}
