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

/**
 * Theme controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

use Magento\Framework\App\ResponseInterface;

class Theme extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_appFileSystem;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Filesystem $appFileSystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Filesystem $appFileSystem
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_assetRepo = $assetRepo;
        $this->_appFileSystem = $appFileSystem;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_eventManager->dispatch('theme_registration_from_filesystem');
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Theme::system_design_theme');
        $this->_view->renderLayout();
    }

    /**
     * Grid ajax action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Create new theme
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit theme
     *
     * @return void
     */
    public function editAction()
    {
        $themeId = (int)$this->getRequest()->getParam('id');
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        try {
            $theme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);
            if ($themeId && (!$theme->load($themeId)->getId() || !$theme->isVisible())) {
                throw new \Magento\Framework\Model\Exception(__('We cannot find theme "%1".', $themeId));
            }
            $this->_coreRegistry->register('current_theme', $theme);

            $this->_view->loadLayout();
            /** @var $tab \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css */
            $tab = $this->_view->getLayout()->getBlock('theme_edit_tabs_tab_css_tab');
            if ($tab && $tab->canShowTab()) {
                /** @var $helper \Magento\Core\Helper\Theme */
                $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
                $files = $helper->getCssAssets($theme);
                $tab->setFiles($files);
            }
            $this->_setActiveMenu('Magento_Theme::system_design_theme');
            $this->_view->renderLayout();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('adminhtml/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot find the theme.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_redirect('adminhtml/*/');
        }
    }

    /**
     * Save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveAction()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        $themeData = $this->getRequest()->getParam('theme');
        $customCssData = $this->getRequest()->getParam('custom_css_content');
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        $reorderJsFiles = array_keys($this->getRequest()->getParam('js_order', array()));

        /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
        /** @var $cssService \Magento\Theme\Model\Theme\Customization\File\CustomCss */
        $cssService = $this->_objectManager->get('Magento\Theme\Model\Theme\Customization\File\CustomCss');
        /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
        $singleFile = $this->_objectManager->create(
            'Magento\Theme\Model\Theme\SingleFile',
            array('fileService' => $cssService)
        );
        try {
            if ($this->getRequest()->getPost()) {
                /** @var $theme \Magento\Core\Model\Theme */
                if (!empty($themeData['theme_id'])) {
                    $theme = $themeFactory->create($themeData['theme_id']);
                } else {
                    $parentTheme = $themeFactory->create($themeData['parent_id']);
                    $theme = $parentTheme->getDomainModel(
                        \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
                    )->createVirtualTheme(
                        $parentTheme
                    );
                }
                if ($theme && !$theme->isEditable()) {
                    throw new \Magento\Framework\Model\Exception(__('Theme isn\'t editable.'));
                }
                $theme->addData($themeData);
                if (isset($themeData['preview']['delete'])) {
                    $theme->getThemeImage()->removePreviewImage();
                }
                $theme->getThemeImage()->uploadPreviewImage('preview');
                $theme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);
                $theme->save();
                $customization = $theme->getCustomization();
                $customization->reorder(
                    \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE,
                    $reorderJsFiles
                );
                $customization->delete($removeJsFiles);
                $singleFile->update($theme, $customCssData);
                $this->messageManager->addSuccess(__('You saved the theme.'));
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setThemeData($themeData);
            $this->_getSession()->setThemeCustomCssData($customCssData);
            $redirectBack = true;
        } catch (\Exception $e) {
            $this->messageManager->addError('The theme was not saved');
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $redirectBack ? $this->_redirect(
            'adminhtml/*/edit',
            array('id' => $theme->getId())
        ) : $this->_redirect(
            'adminhtml/*/'
        );
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        $redirectBack = (bool)$this->getRequest()->getParam('back', false);
        $themeId = $this->getRequest()->getParam('id');
        try {
            if ($themeId) {
                /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
                $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface')->load($themeId);
                if (!$theme->getId()) {
                    throw new \InvalidArgumentException(sprintf('We cannot find a theme with id "%1".', $themeId));
                }
                if (!$theme->isVirtual()) {
                    throw new \InvalidArgumentException(
                        sprintf('Only virtual theme is possible to delete and theme "%s" isn\'t virtual', $themeId)
                    );
                }
                $theme->delete();
                $this->messageManager->addSuccess(__('You deleted the theme.'));
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot delete the theme.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        /**
         * @todo Temporary solution. Theme module should not know about the existence of editor module.
         */
        $redirectBack ? $this->_redirect('adminhtml/system_design_editor/index/') : $this->_redirect('adminhtml/*/');
    }

    /**
     * Upload css file
     *
     * @return void
     */
    public function uploadCssAction()
    {
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        try {
            $cssFileContent = $serviceModel->uploadCssFile('css_file_uploader');
            $result = array('error' => false, 'content' => $cssFileContent['content']);
        } catch (\Magento\Framework\Model\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('We cannot upload the CSS file.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Upload js file
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadJsAction()
    {
        $themeId = $this->getRequest()->getParam('id');
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
        /** @var $jsService \Magento\Framework\View\Design\Theme\Customization\File\Js */
        $jsService = $this->_objectManager->get('Magento\Framework\View\Design\Theme\Customization\File\Js');
        try {
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new \Magento\Framework\Model\Exception(__('We cannot find a theme with id "%1".', $themeId));
            }
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($theme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();

            /** @var $customization \Magento\Framework\View\Design\Theme\Customization */
            $customization = $this->_objectManager->create(
                'Magento\Framework\View\Design\Theme\CustomizationInterface',
                array('theme' => $theme)
            );
            $customJsFiles = $customization->getFilesByType(
                \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE
            );
            $result = array('error' => false, 'files' => $customization->generateFileInfo($customJsFiles));
        } catch (\Magento\Framework\Model\Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $result = array('error' => true, 'message' => __('We cannot upload the JS file.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Download custom css file
     *
     * @return ResponseInterface|void
     */
    public function downloadCustomCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        try {
            /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
            $themeFactory = $this->_objectManager->create('Magento\Framework\View\Design\Theme\FlyweightFactory');
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new \InvalidArgumentException(sprintf('We cannot find a theme with id "%1".', $themeId));
            }

            $customCssFiles = $theme->getCustomization()->getFilesByType(
                \Magento\Theme\Model\Theme\Customization\File\CustomCss::TYPE
            );
            /** @var $customCssFile \Magento\Framework\View\Design\Theme\FileInterface */
            $customCssFile = reset($customCssFiles);
            if ($customCssFile && $customCssFile->getContent()) {
                return $this->_fileFactory->create(
                    $customCssFile->getFileName(),
                    array('type' => 'filename', 'value' => $customCssFile->getFullPath()),
                    \Magento\Framework\App\Filesystem::ROOT_DIR
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot find file'));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }

    /**
     * Download css file
     *
     * @return ResponseInterface|void
     */
    public function downloadCssAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $file = $this->getRequest()->getParam('file');

        /** @var $helper \Magento\Core\Helper\Theme */
        $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
        $fileId = $helper->urlDecode($file);
        try {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface')->load($themeId);
            if (!$theme->getId()) {
                throw new \InvalidArgumentException(sprintf('Theme not found: "%1".', $themeId));
            }
            $asset = $this->_assetRepo->createAsset($fileId, array('themeModel' => $theme));
            $relPath = $this->_appFileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR)
                ->getRelativePath($asset->getSourceFile());
            return $this->_fileFactory->create(
                $relPath,
                array(
                    'type'  => 'filename',
                    'value' => $relPath
                ),
                \Magento\Framework\App\Filesystem::ROOT_DIR
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('File not found: "%1".', $fileId));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }

    /**
     * Check the permission to manage themes
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Theme::theme');
    }
}
