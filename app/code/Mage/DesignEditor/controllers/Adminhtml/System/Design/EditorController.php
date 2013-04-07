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
 */
class Mage_DesignEditor_Adminhtml_System_Design_EditorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Display the design editor launcher page
     */
    public function indexAction()
    {
        $this->_doSelectionTheme('firstEntrance');
    }

    /**
     * Ajax loading available themes
     */
    public function loadThemeListAction()
    {
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = $this->_objectManager->get('Mage_Core_Helper_Data');

        $page = $this->getRequest()->getParam('page', 1);
        $pageSize = $this->getRequest()
            ->getParam('page_size', Mage_Core_Model_Resource_Theme_Collection::DEFAULT_PAGE_SIZE);

        try {
            $this->loadLayout();
            /** @var $service Mage_Core_Model_Theme_Service */
            $service = $this->_objectManager->get('Mage_Core_Model_Theme_Service');

            /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
            $collection = $service->getPhysicalThemes($page, $pageSize);
            $this->getLayout()->getBlock('available.theme.list')->setCollection($collection)->setNextPage(++$page);
            $response = array('content' => $this->getLayout()->getOutput());
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $response = array('error' => $this->_helper->__('Theme list can not be loaded'));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Activate the design editor in the session and redirect to the frontend of the selected store
     */
    public function launchAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id', $this->_getSession()->getData('theme_id'));
        $mode = (string)$this->getRequest()->getParam('mode', Mage_DesignEditor_Model_State::MODE_DESIGN);
        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        try {
            $theme = $helper->loadVisibleTheme($themeId);
            $editableTheme = $theme->isVirtual() ? $theme->getDomainModel()->getStagingTheme() : $theme;
            $this->_getSession()->setData(Mage_DesignEditor_Model_State::VIRTUAL_THEME_SESSION_KEY, $themeId);
            $this->_getSession()->setData(
                Mage_DesignEditor_Model_State::CURRENT_THEME_SESSION_KEY, $editableTheme->getId()
            );

            /** @var $eventDispatcher Mage_Core_Model_Event_Manager */
            $eventDispatcher = $this->_objectManager->get('Mage_Core_Model_Event_Manager');
            $eventDispatcher->dispatch('design_editor_activate');

            $this->_setTitle();
            $this->loadLayout();

            $this->_configureToolbarBlocks($theme, $editableTheme, $mode);      //top panel
            $this->_configureToolsBlocks($editableTheme, $mode);                //bottom panel
            $this->_configureEditorBlock($editableTheme, $mode);                //editor container

            $redirectOnAssign = $theme->isPhysical();
            /** @var $storeViewBlock Mage_DesignEditor_Block_Adminhtml_Theme_Selector_StoreView */
            $storeViewBlock = $this->getLayout()->getBlock('theme.selector.storeview');
            $storeViewBlock->setData(array(
                'redirectOnAssign' => $redirectOnAssign,
                'openNewOnAssign'  => false,
                'theme_id'         => $theme->getId()
            ));

            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Unknown error'));
            $this->_redirect('*/*/');
            return;
        }
    }

    /**
     * Create virtual theme action
     */
    public function createVirtualThemeAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id', false);
        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        try {
            $theme = $helper->loadVisibleTheme($themeId);
            if (!$theme->getId() || ($theme->getType() != Mage_Core_Model_Theme::TYPE_PHYSICAL)) {
                throw new Mage_Core_Exception($this->__('Theme "%s" was not found.', $theme->getId()));
            }
            $virtualTheme = $this->_getThemeCustomization($theme);
            $response = array(
                'error'         => false,
                'redirect_url'  => $this->getUrl('*/*/launch', array('theme_id' => $virtualTheme->getId()))
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Unknown error.');
            $this->_getSession()->addException($e, $errorMessage);
            $response = array('error' => true, 'message' => $errorMessage);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * VDE quit action
     */
    public function quitAction()
    {
        /** @var $state Mage_DesignEditor_Model_State */
        $state = $this->_objectManager->get('Mage_DesignEditor_Model_State');
        $state->reset();

        /** @var $eventDispatcher Mage_Core_Model_Event_Manager */
        $eventDispatcher = $this->_objectManager->get('Mage_Core_Model_Event_Manager');
        $eventDispatcher->dispatch('design_editor_deactivate');

        $this->_redirect('*/*/');
    }

    /**
     * Assign theme to list of store views
     */
    public function assignThemeToStoreAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $stores = $this->getRequest()->getParam('stores');

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = $this->_objectManager->get('Mage_Core_Helper_Data');

        try {
            if (!is_numeric($themeId)) {
                throw new InvalidArgumentException('Theme id is not valid');
            }

            //TODO used until we find a way to convert array to JSON on JS side
            $defaultStore = -1;
            $emptyStores = -2;
            if ($stores == $defaultStore) {
                $ids = array_keys(Mage::app()->getStores());
                $stores = array(array_shift($ids));
            } elseif ($stores == $emptyStores) {
                $stores = array();
            }

            if (!is_array($stores)) {
                throw new InvalidArgumentException('Param "stores" is not valid');
            }

            /** @var $themeService Mage_Core_Model_Theme_Service */
            $themeService = $this->_objectManager->get('Mage_Core_Model_Theme_Service');
            /** @var $themeCustomization Mage_Core_Model_Theme */
            $themeCustomization = $themeService->assignThemeToStores($themeId, $stores);

            $message = $coreHelper->__('Theme successfully assigned');
            $response = array(
                'success' => $message,
                'themeId' => $themeCustomization->getId()
            );
            $this->getResponse()->setBody($coreHelper->jsonEncode(array('success' => $message)));
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $this->getResponse()->setBody($coreHelper->jsonEncode(
                array('error' => $this->_helper->__('Theme is not assigned'))
            ));
            $response = array(
                'error'   => true,
                'message' => $this->_helper->__('Theme is not assigned')
            );
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Rename title action
     */
    public function quickEditAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $themeTitle = (string)$this->getRequest()->getParam('theme_title');

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = $this->_objectManager->get('Mage_Core_Helper_Data');
        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        try {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $helper->loadEditableTheme($themeId);
            $theme->setThemeTitle($themeTitle);
            $theme->save();
            $response = array('success' => true);
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $response = array('error' => true, 'message' => $this->__('Theme is not saved'));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Get layout xml
     */
    public function getLayoutUpdateAction()
    {
        $historyData = Mage::app()->getRequest()->getPost('historyData');
        if (!$historyData) {
            $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode(
                array(Mage_Core_Model_Message::ERROR => array($this->__('Invalid post data')))
            ));
            return;
        }

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = $this->_objectManager->get('Mage_Core_Helper_Data');

        try {
            $layoutUpdate = $this->_compactHistory($historyData);
            $response = array(Mage_Core_Model_Message::SUCCESS => array($layoutUpdate));
        } catch (Mage_Core_Exception $e) {
            $response = array(Mage_Core_Model_Message::ERROR => array($e->getMessage()));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Save temporary layout update
     */
    public function saveTemporaryLayoutUpdateAction()
    {
        $themeId = (int)$this->_getSession()->getData('theme_id');
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = $this->_objectManager->get('Mage_Core_Helper_Data');

        try {
            if (!is_numeric($themeId)) {
                throw new InvalidArgumentException('Theme id is not valid');
            }

            if ($this->getRequest()->has('layoutUpdate')) {
                $this->_saveLayoutUpdate(
                    $this->getRequest()->getParam('layoutUpdate'),
                    $this->getRequest()->getParam('handle'),
                    $themeId,
                    true
                );
            }
            $response = array('success' => $this->__('Temporary layout update saved'));
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $response = array('error' => $this->__('Temporary layout update not saved'));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Display available theme list. Only when no customized themes
     */
    public function firstEntranceAction()
    {
        $this->_doSelectionTheme('index');
    }

    /**
     * Simple Theme preview
     */
    public function previewAction()
    {
        $this->launchAction();
    }

    /**
     * Apply changes from 'staging' theme to 'virtual' theme
     */
    public function saveAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = $this->_objectManager->get('Mage_Core_Helper_Data');
        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        try {
            $theme = $helper->loadEditableTheme($themeId)
                ->getDomainModel(Mage_Core_Model_Theme::TYPE_VIRTUAL)
                ->getStagingTheme();
            $this->_saveLayoutUpdate(
                $this->getRequest()->getParam('layoutUpdate', array()),
                $this->getRequest()->getParam('handle'),
                $theme->getId()
            );
            $theme->getDomainModel(Mage_Core_Model_Theme::TYPE_STAGING)->updateFromStagingTheme();
            $response = array('message' =>  $this->_helper->__('All changes applied'));
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
            $response = array('error' => true, 'message' => $this->_helper->__('Unknown error'));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Set page title
     */
    protected function _setTitle()
    {
        $this->_title($this->__('System'))->_title($this->__('Design'))->_title($this->__('Editor'));
    }

    /**
     * Whether the current user has enough permissions to execute an action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_objectManager->get('Mage_Core_Model_Authorization')->isAllowed('Mage_DesignEditor::editor');
    }

    /**
     * Compact history
     *
     * @param array $layoutUpdate
     * @param string|null $xml
     * @return string
     */
    protected function _compactHistory($layoutUpdate, $xml = null)
    {
        /** @var $historyModel Mage_DesignEditor_Model_History */
        $historyModel = $this->_objectManager->create('Mage_DesignEditor_Model_History');
        /** @var $historyCompactModel Mage_DesignEditor_Model_History_Compact */
        $historyCompactModel = $this->_objectManager->create('Mage_DesignEditor_Model_History_Compact');
        /** @var $layoutRenderer Mage_DesignEditor_Model_History_Renderer_LayoutUpdate */
        $layoutRenderer = $this->_objectManager->create('Mage_DesignEditor_Model_History_Renderer_LayoutUpdate');
        /** @var $collection Mage_DesignEditor_Model_Change_Collection */
        $collection = $historyModel->addXmlChanges($xml)
            ->addChanges($layoutUpdate)
            ->getChanges();
        $historyCompactModel->compact($collection);
        $layoutUpdate = $historyModel->output($layoutRenderer,
            Mage_DesignEditor_Model_History_Renderer_LayoutUpdate::DEFAULT_HANDLE
        );

        return $layoutUpdate;
    }

    /**
     * Save layout update
     *
     * @param array $layoutUpdate
     * @param string $handle
     * @param int $themeId
     * @param bool $isTemporary
     */
    protected function _saveLayoutUpdate($layoutUpdate, $handle, $themeId, $isTemporary = false)
    {
        /** @var $layoutCollection Mage_DesignEditor_Model_Resource_Layout_Update_Collection */
        $layoutCollection = $this->_objectManager
            ->create('Mage_DesignEditor_Model_Resource_Layout_Update_Collection');
        $layoutCollection->addStoreFilter(Mage_Core_Model_AppInterface::ADMIN_STORE_ID)
            ->addThemeFilter($themeId)
            ->addFieldToFilter('handle', $handle)
            ->addFieldToFilter('is_vde', true)
            ->setOrder('sort_order', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $xml = '';
        if (!$isTemporary) {
            /** @var $item Mage_DesignEditor_Model_Layout_Update */
            foreach ($layoutCollection as $item) {
                $xml .= $item->getXml();
            }
        }

        if ($xml || $layoutUpdate) {
            $layoutUpdateData = array(
                'store_id'     => Mage_Core_Model_AppInterface::ADMIN_STORE_ID,
                'theme_id'     => $themeId,
                'handle'       => $handle,
                'xml'          => $this->_compactHistory($layoutUpdate, $xml),
                'is_temporary' => $isTemporary
            );

            if ($isTemporary) {
                /** @var $layoutUpdateModel Mage_DesignEditor_Model_Layout_Update */
                $layoutUpdateModel = $layoutCollection->getLastItem();
                $sortOrder = 0;
                if ($layoutUpdateModel->getId()) {
                    $sortOrder = $layoutUpdateModel->getSortOrder() + 1;
                }
                $layoutUpdateData['sort_order'] = $sortOrder;
                $layoutUpdateModel->setData($layoutUpdateData);
            } else {
                /** @var $layoutUpdateModel Mage_DesignEditor_Model_Layout_Update */
                $layoutUpdateModel = $layoutCollection->getFirstItem();
                $layoutUpdateModel->addData($layoutUpdateData);

                /** @var @item Mage_DesignEditor_Model_Layout_Update */
                foreach ($layoutCollection as $item) {
                    if ($item->getId() != $layoutUpdateModel->getId()) {
                        $item->delete();
                    }
                }
            }
            $layoutUpdateModel->save();
        }
    }

    /**
     * Get theme customization
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     */
    protected function _getThemeCustomization($theme)
    {
        /** @var $service Mage_Core_Model_Theme_Service */
        $service = $this->_objectManager->get('Mage_Core_Model_Theme_Service');
        return $service->createThemeCustomization($theme);
    }

    /**
     * Pass data to the Tools panel blocks that is needed it for rendering
     *
     * @param Mage_Core_Model_Theme $theme
     * @param int $mode
     * @return Mage_DesignEditor_Adminhtml_System_Design_EditorController
     */
    protected function _configureToolsBlocks($theme, $mode)
    {
        /** @var $toolsBlock Mage_DesignEditor_Block_Adminhtml_Editor_Tools */
        $toolsBlock = $this->getLayout()->getBlock('design_editor_tools');
        if ($toolsBlock) {
            $toolsBlock->setMode($mode);
        }

        /** @var $customTabBlock Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Custom */
        $customTabBlock = $this->getLayout()->getBlock('design_editor_tools_code_custom');
        if ($customTabBlock) {
            $theme->setCustomization($this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css'));
            $customTabBlock->setTheme($theme);
        }

        /** @var $customTabBlock Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Custom */
        $customTabBlock = $this->getLayout()->getBlock('design_editor_tools_code_custom');
        if ($customTabBlock) {
            $theme->setCustomization($this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css'));
            $customTabBlock->setTheme($theme);
        }

        /** @var $cssTabBlock Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Css */
        $cssTabBlock = $this->getLayout()->getBlock('design_editor_tools_code_css');
        if ($cssTabBlock) {
            /** @var $helper Mage_Core_Helper_Theme */
            $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
            $cssFiles = $helper->getGroupedCssFiles($theme);
            $cssTabBlock->setCssFiles($cssFiles)
                ->setThemeId($theme->getId());
        }

        /** @var $jsTabBlock Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js */
        $jsTabBlock = $this->getLayout()->getBlock('design_editor_tools_code_js');
        if ($jsTabBlock) {
            /** @var $jsFileModel Mage_Core_Model_Theme_Customization_Files_Js */
            $jsFileModel = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');
            $theme->setCustomization($jsFileModel);

            $jsTabBlock->setTheme($theme);
        }

        $blocks = array(
            'design_editor_tools_code_image_sizing',
            'design_editor_tools_quick-styles_header',
            'design_editor_tools_quick-styles_backgrounds',
            'design_editor_tools_quick-styles_buttons',
            'design_editor_tools_quick-styles_tips',
            'design_editor_tools_quick-styles_fonts',
        );
        foreach ($blocks as $blockName) {
            /** @var $block Mage_Core_Block_Abstract */
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                $block->setTheme($theme);
            }
        }

        return $this;
    }

    /**
     * Pass data to the Toolbar panel blocks that is needed for rendering
     *
     * @param Mage_Core_Model_Theme $theme
     * @param Mage_Core_Model_Theme $editableTheme
     * @param int $mode
     * @return Mage_DesignEditor_Adminhtml_System_Design_EditorController
     */
    protected function _configureToolbarBlocks($theme, $editableTheme, $mode)
    {
        /** @var $toolbarBlock Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons */
        $toolbarBlock = $this->getLayout()->getBlock('design_editor_toolbar_buttons');
        $toolbarBlock->setThemeId($editableTheme->getId())->setVirtualThemeId($theme->getId())
            ->setMode($mode);

        /** @var $saveButtonBlock Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons_Save */
        $saveButtonBlock = $this->getLayout()->getBlock('design_editor_toolbar_buttons_save');
        if ($saveButtonBlock) {
            $saveButtonBlock->setTheme($theme)
                ->setMode($mode);
        }

        /** @var $hierarchyBlock Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchy */
        $hierarchyBlock = $this->getLayout()->getBlock('design_editor_toolbar_handles_hierarchy');
        if ($hierarchyBlock) {
            $customLayoutParams = array('area' => Mage_Core_Model_App_Area::AREA_FRONTEND);

            /** @var $customFrontLayout Mage_Core_Model_Layout_Merge */
            $customFrontLayout = $this->_objectManager->create('Mage_Core_Model_Layout_Merge',
                array('arguments' => $customLayoutParams)
            );
            $pageTypes = $customFrontLayout->getPageHandlesHierarchy();
            $hierarchyBlock->setHierarchy($pageTypes)
                ->setMode($mode);
        }

        /** @var $viewOptionsBlock Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_ViewOptions */
        $viewOptionsBlock = $this->getLayout()->getBlock('design_editor_toolbar_view_options');
        if ($viewOptionsBlock) {
            $viewOptionsBlock->setMode($mode);
        }

        return $this;
    }

    /**
     * @param Mage_Core_Model_Theme $editableTheme
     * @param int $mode
     * @return Mage_DesignEditor_Adminhtml_System_Design_EditorController
     */
    protected function _configureEditorBlock($editableTheme, $mode)
    {
        /** @var $editorBlock Mage_DesignEditor_Block_Adminhtml_Editor_Container */
        $editorBlock = $this->getLayout()->getBlock('design_editor');
        if ($mode == Mage_DesignEditor_Model_State::MODE_NAVIGATION) {
            $currentUrl = $this->_getCurrentUrl();
        } else {
            $currentUrl = $this->_getCurrentHandleUrl();
        }
        $editorBlock->setFrameUrl($currentUrl);
        $editorBlock->setTheme($editableTheme);

        return $this;
    }

    /**
     * Check whether is customized themes in database
     *
     * @return bool
     */
    protected function _isFirstEntrance()
    {
        /** @var $themeService Mage_Core_Model_Theme_Service */
        $themeService = $this->_objectManager->get('Mage_Core_Model_Theme_Service');
        return !$themeService->isCustomizationsExist();
    }

    /**
     * Load layout
     *
     * @param string $forwardAction
     */
    protected function _doSelectionTheme($forwardAction)
    {
        if ($forwardAction == 'index' xor $this->_isFirstEntrance()) {
            $this->_forward($forwardAction);
            return;
        }

        try {
            $this->_setTitle();
            $this->loadLayout();
            $this->_setActiveMenu('Mage_DesignEditor::system_design_editor');
            if (!$this->_isFirstEntrance()) {
                /** @var $themeService Mage_Core_Model_Theme_Service */
                $themeService = $this->_objectManager->get('Mage_Core_Model_Theme_Service');
                $this->getLayout()->getBlock('assigned.theme.list')->setCollection(
                    $themeService->getAssignedThemeCustomizations()
                );
                $this->getLayout()->getBlock('unassigned.theme.list')->setCollection(
                    $themeService->getUnassignedThemeCustomizations()
                );
            }
            /** @var $storeViewBlock Mage_DesignEditor_Block_Adminhtml_Theme_Selector_StoreView */
            $storeViewBlock = $this->getLayout()->getBlock('theme.selector.storeview');
            $storeViewBlock->setData('redirectOnAssign', true);
            $this->renderLayout();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot load list of themes.'));
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
    }

    /**
     * Get current handle
     *
     * @return string
     */
    protected function _getCurrentHandleUrl()
    {
        /** @var $vdeUrlModel Mage_DesignEditor_Model_Url_Handle */
        $vdeUrlModel = $this->_objectManager->get('Mage_DesignEditor_Model_Url_Handle');
        $handle = $this->_getSession()->getData(Mage_DesignEditor_Model_State::CURRENT_HANDLE_SESSION_KEY);
        if (empty($handle)) {
            $handle = 'default';
        }

        return $vdeUrlModel->getUrl('design/page/type', array('handle' => $handle));
    }

    /**
     * Get current url
     *
     * @return string
     */
    protected function _getCurrentUrl()
    {
        /** @var $vdeUrlModel Mage_DesignEditor_Model_Url_NavigationMode */
        $vdeUrlModel = $this->_objectManager->get('Mage_DesignEditor_Model_Url_NavigationMode');
        $url = $this->_getSession()->getData(Mage_DesignEditor_Model_State::CURRENT_URL_SESSION_KEY);
        if (empty($url)) {
            $url = '';
        }

        return $vdeUrlModel->getUrl(ltrim($url, '/'));
    }
}
