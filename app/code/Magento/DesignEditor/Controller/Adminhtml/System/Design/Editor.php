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
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\DesignEditor\Controller\Adminhtml\System\Design;

/**
 * Backend controller for the design editor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Editor extends \Magento\Adminhtml\Controller\Action
{
    /**
     * @var \Magento\Theme\Model\Config
     */
    protected $_themeConfig;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_customizationConfig;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Theme\Model\Config $themeConfig
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Theme\Model\Config $themeConfig,
        \Magento\Theme\Model\Config\Customization $customizationConfig
    ) {
        $this->_themeConfig         = $themeConfig;
        $this->_customizationConfig = $customizationConfig;

        parent::__construct($context);
    }

    /**
     * Display the design editor launcher page
     */
    public function indexAction()
    {
        if (!$this->_resolveForwarding()) {
            $this->_renderStoreDesigner();
        }
    }

    /**
     * Ajax loading available themes
     */
    public function loadThemeListAction()
    {
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');

        $page = $this->getRequest()->getParam('page', 1);
        $pageSize = $this->getRequest()
            ->getParam('page_size', \Magento\Core\Model\Resource\Theme\Collection::DEFAULT_PAGE_SIZE);

        try {
            $this->loadLayout();
            /** @var $collection \Magento\Core\Model\Resource\Theme\Collection */
            $collection = $this->_objectManager->get('Magento\Core\Model\Resource\Theme\Collection')
                ->filterPhysicalThemes($page, $pageSize);

            /** @var $availableThemeBlock \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Available */
            $availableThemeBlock =  $this->getLayout()->getBlock('available.theme.list');
            $availableThemeBlock->setCollection($collection)->setNextPage(++$page);
            $availableThemeBlock->setIsFirstEntrance($this->_isFirstEntrance());
            $availableThemeBlock->setHasThemeAssigned($this->_customizationConfig->hasThemeAssigned());

            $response = array('content' => $this->getLayout()->getOutput());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $response = array('error' => __('Sorry, but we can\'t load the theme list.'));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Activate the design editor in the session and redirect to the frontend of the selected store
     */
    public function launchAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $mode = (string)$this->getRequest()->getParam('mode', \Magento\DesignEditor\Model\State::MODE_NAVIGATION);
        try {
            /** @var \Magento\DesignEditor\Model\Theme\Context $themeContext */
            $themeContext = $this->_objectManager->get('Magento\DesignEditor\Model\Theme\Context');
            $themeContext->setEditableThemeById($themeId);
            $launchedTheme = $themeContext->getEditableTheme();
            if ($launchedTheme->isPhysical()) {
                $launchedTheme = $launchedTheme->getDomainModel(\Magento\Core\Model\Theme::TYPE_PHYSICAL)
                    ->createVirtualTheme($launchedTheme);
                $this->_redirect($this->getUrl('*/*/*', array('theme_id' => $launchedTheme->getId())));
                return;
            }
            $editableTheme = $themeContext->getStagingTheme();

            $this->_eventManager->dispatch('design_editor_activate');

            $this->_setTitle();
            $this->loadLayout();

            $this->_configureToolbarBlocks($launchedTheme, $editableTheme, $mode); //top panel
            $this->_configureToolsBlocks($launchedTheme, $mode); //bottom panel
            $this->_configureEditorBlock($launchedTheme, $mode); //editor container

            /** @var $storeViewBlock \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\StoreView */
            $storeViewBlock = $this->getLayout()->getBlock('theme.selector.storeview');
            $storeViewBlock->setData(array(
                'actionOnAssign' => 'none',
                'theme_id'       => $launchedTheme->getId()
            ));

            $this->renderLayout();
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_redirect('*/*/');
            return;
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Sorry, there was an unknown error.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_redirect('*/*/');
            return;
        }
    }

    /**
     * Assign theme to list of store views
     */
    public function assignThemeToStoreAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $reportToSession = (bool)$this->getRequest()->getParam('reportToSession');

        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');

        $hadThemeAssigned = $this->_customizationConfig->hasThemeAssigned();

        try {
            $theme = $this->_loadThemeById($themeId);

            $themeCustomization = $theme->isVirtual()
                ? $theme
                : $theme->getDomainModel(\Magento\Core\Model\Theme::TYPE_PHYSICAL)->createVirtualTheme($theme);

            /** @var $themeCustomization \Magento\View\Design\ThemeInterface */
            $this->_themeConfig->assignToStore($themeCustomization, $this->_getStores());

            $successMessage = $hadThemeAssigned
                ? __('You assigned a new theme to your store view.')
                : __('You assigned a theme to your live store.');
            if ($reportToSession) {
                $this->_getSession()->addSuccess($successMessage);
            }
            $response = array(
                'message' => $successMessage,
                'themeId' => $themeCustomization->getId()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->getResponse()->setBody($coreHelper->jsonEncode(
                array('error' => __('This theme is not assigned.'))
            ));
            $response = array(
                'error'   => true,
                'message' => __('This theme is not assigned.')
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

        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
        try {
            $theme = $this->_loadThemeById($themeId);
            if (!$theme->isEditable()) {
                throw new \Magento\Core\Exception(__('Sorry, but you can\'t edit theme "%1".',
                    $theme->getThemeTitle()));
            }
            $theme->setThemeTitle($themeTitle);
            $theme->save();
            $response = array('success' => true);
        } catch (\Magento\Core\Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $response = array('error' => true, 'message' => __('This theme is not saved.'));
        }
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Display available theme list. Only when no customized themes
     */
    public function firstEntranceAction()
    {
        if (!$this->_resolveForwarding()) {
            $this->_renderStoreDesigner();
        }
    }

    /**
     * Apply changes from 'staging' theme to 'virtual' theme
     */
    public function saveAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');

        /** @var \Magento\DesignEditor\Model\Theme\Context $themeContext */
        $themeContext = $this->_objectManager->get('Magento\DesignEditor\Model\Theme\Context');
        $themeContext->setEditableThemeById($themeId);
        try {
            $themeContext->copyChanges();
            if ($this->_customizationConfig->isThemeAssignedToStore($themeContext->getEditableTheme())) {
                $message = __('You updated your live store.');
            } else {
                $message = __('You saved updates to this theme.');
            }
            $response = array('message' =>  $message);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $response = array('error' => true, 'message' => __('Sorry, there was an unknown error.'));
        }

        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Duplicate theme action
     */
    public function duplicateAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        /** @var $themeCopy \Magento\View\Design\ThemeInterface */
        $themeCopy = $this->_objectManager->create('Magento\View\Design\ThemeInterface');
        /** @var $copyService \Magento\Core\Model\Theme\CopyService */
        $copyService = $this->_objectManager->get('Magento\Core\Model\Theme\CopyService');
        try {
            $theme = $this->_loadThemeById($themeId);
            if (!$theme->isVirtual()) {
                throw new \Magento\Core\Exception(__('Sorry, but you can\'t edit theme "%1".',
                    $theme->getThemeTitle()));
            }
            $themeCopy->setData($theme->getData());
            $themeCopy->setId(null)->setThemeTitle(__('Copy of [%1]', $theme->getThemeTitle()));
            $themeCopy->getThemeImage()->createPreviewImageCopy($theme->getPreviewImage());
            $themeCopy->save();
            $copyService->copy($theme, $themeCopy);
            $this->_getSession()->addSuccess(
                __('You saved a duplicate copy of this theme in "My Customizations."')
            );
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_getSession()->addError(__('You cannot duplicate this theme.'));
        }
        $this->_redirectUrl($this->_getRefererUrl());
    }

    /**
     * Revert 'staging' theme to the state of 'physical' or 'virtual'
     *
     * @throws \Magento\Core\Exception
     */
    public function revertAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $revertTo = $this->getRequest()->getParam('revert_to');

        $virtualTheme = $this->_loadThemeById($themeId);
        if (!$virtualTheme->isVirtual()) {
            throw new \Magento\Core\Exception(__('Theme "%1" is not editable.', $virtualTheme->getId()));
        }

        try {
            /** @var $copyService \Magento\Core\Model\Theme\CopyService */
            $copyService = $this->_objectManager->get('Magento\Core\Model\Theme\CopyService');
            $stagingTheme = $virtualTheme->getDomainModel(\Magento\Core\Model\Theme::TYPE_VIRTUAL)->getStagingTheme();
            switch ($revertTo) {
                case 'last_saved':
                    $copyService->copy($virtualTheme, $stagingTheme);
                    $message = __('Theme "%1" reverted to last saved state',
                        $virtualTheme->getThemeTitle()
                    );
                    break;

                case 'physical':
                    $physicalTheme = $virtualTheme->getDomainModel(\Magento\Core\Model\Theme::TYPE_VIRTUAL)
                        ->getPhysicalTheme();
                    $copyService->copy($physicalTheme, $stagingTheme);
                    $message = __('Theme "%1" reverted to last default state',
                        $virtualTheme->getThemeTitle()
                    );
                    break;

                default:
                    throw new \Magento\Exception('Invalid revert mode "%s"', $revertTo);
            }
            $response = array('message' => $message);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $response = array('error' => true, 'message' => __('Unknown error'));
        }
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
        $this->getResponse()->setBody($coreHelper->jsonEncode($response));
    }

    /**
     * Set page title
     */
    protected function _setTitle()
    {
        $this->_title(__('Store Designer'));
    }

    /**
     * Load theme by id
     *
     * @param int $themeId
     * @return \Magento\Core\Model\Theme
     * @throws \Magento\Core\Exception
     */
    protected function _loadThemeById($themeId)
    {
        /** @var $themeFactory \Magento\Core\Model\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->create('Magento\Core\Model\Theme\FlyweightFactory');
        $theme = $themeFactory->create($themeId);
        if (empty($theme)) {
            throw new \Magento\Core\Exception(__('We can\'t find this theme.'));
        }
        return $theme;
    }

    /**
     * Whether the current user has enough permissions to execute an action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_DesignEditor::editor');
    }

    /**
     * Pass data to the Tools panel blocks that is needed it for rendering
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param string $mode
     * @return \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
     */
    protected function _configureToolsBlocks($theme, $mode)
    {
        /** @var $toolsBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Tools */
        $toolsBlock = $this->getLayout()->getBlock('design_editor_tools');
        if ($toolsBlock) {
            $toolsBlock->setMode($mode);
        }

        /** @var $cssTabBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Css */
        $cssTabBlock = $this->getLayout()->getBlock('design_editor_tools_code_css');
        if ($cssTabBlock) {
            /** @var $helper \Magento\Core\Helper\Theme */
            $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
            $cssFiles = $helper->getGroupedCssFiles($theme);
            $cssTabBlock->setCssFiles($cssFiles)
                ->setThemeId($theme->getId());
        }
        return $this;
    }

    /**
     * Pass data to the Toolbar panel blocks that is needed for rendering
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param \Magento\View\Design\ThemeInterface $editableTheme
     * @param string $mode
     * @return \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
     */
    protected function _configureToolbarBlocks($theme, $editableTheme, $mode)
    {
        /** @var $toolbarBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons */
        $toolbarBlock = $this->getLayout()->getBlock('design_editor_toolbar_buttons');
        $toolbarBlock->setThemeId($editableTheme->getId())->setVirtualThemeId($theme->getId())
            ->setMode($mode);

        /** @var $saveButtonBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save */
        $saveButtonBlock = $this->getLayout()->getBlock('design_editor_toolbar_buttons_save');
        if ($saveButtonBlock) {
            $saveButtonBlock->setTheme($theme)->setMode($mode)->setHasThemeAssigned(
                $this->_customizationConfig->hasThemeAssigned()
            );
        }
        /** @var $saveButtonBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Edit */
        $editButtonBlock = $this->getLayout()->getBlock('design_editor_toolbar_buttons_edit');
        if ($editButtonBlock) {
            $editButtonBlock->setTheme($editableTheme);
        }

        return $this;
    }

    /**
     * Set to iframe block selected mode and theme
     *
     * @param \Magento\View\Design\ThemeInterface $editableTheme
     * @param string $mode
     * @return \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
     */
    protected function _configureEditorBlock($editableTheme, $mode)
    {
        /** @var $editorBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Container */
        $editorBlock = $this->getLayout()->getBlock('design_editor');
        $currentUrl = $this->_getCurrentUrl($editableTheme->getId(), $mode);
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
        $isCustomized = (bool)$this->_objectManager->get('Magento\Core\Model\Resource\Theme\CollectionFactory')
            ->create()
            ->addTypeFilter(\Magento\Core\Model\Theme::TYPE_VIRTUAL)
            ->getSize();
        return !$isCustomized;
    }

    /**
     * Load layout
     */
    protected function _renderStoreDesigner()
    {
        try {
            $this->_setTitle();
            $this->loadLayout();
            $this->_setActiveMenu('Magento_DesignEditor::system_design_editor');
            if (!$this->_isFirstEntrance()) {
                /** @var $assignedThemeBlock
                 * \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Assigned */
                $assignedThemeBlock = $this->getLayout()->getBlock('assigned.theme.list');
                $assignedThemeBlock->setCollection($this->_customizationConfig->getAssignedThemeCustomizations());

                /** @var $unassignedThemeBlock
                 * \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Unassigned */
                $unassignedThemeBlock = $this->getLayout()->getBlock('unassigned.theme.list');
                $unassignedThemeBlock->setCollection($this->_customizationConfig->getUnassignedThemeCustomizations());
                $unassignedThemeBlock->setHasThemeAssigned($this->_customizationConfig->hasThemeAssigned());
            }
            /** @var $storeViewBlock \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\StoreView */
            $storeViewBlock = $this->getLayout()->getBlock('theme.selector.storeview');
            $storeViewBlock->setData('actionOnAssign', 'refresh');
            $this->renderLayout();
        } catch (\Exception $e) {
            $this->_getSession()->addError(__('We can\'t load the list of themes.'));
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        }
    }

    /**
     * Resolve which action should be actually performed and forward to it
     *
     * @return bool Is forwarding was done
     */
    protected function _resolveForwarding()
    {
        $action = $this->_isFirstEntrance() ? 'firstEntrance' : 'index';
        if ($action != $this->getRequest()->getActionName()) {
            $this->_forward($action);
            return true;
        };

        return false;
    }

    /**
     * Get current url
     *
     * @param null|string $themeId
     * @param null|string $mode
     * @return string
     */
    protected function _getCurrentUrl($themeId = null, $mode = null)
    {
        /** @var $vdeUrlModel \Magento\DesignEditor\Model\Url\NavigationMode */
        $vdeUrlModel = $this->_objectManager->create('Magento\DesignEditor\Model\Url\NavigationMode', array(
             'data' => array('mode' => $mode, 'themeId' => $themeId)
        ));
        $url = $this->_getSession()->getData(\Magento\DesignEditor\Model\State::CURRENT_URL_SESSION_KEY);
        if (empty($url)) {
            $url = '';
        }
        return $vdeUrlModel->getUrl(ltrim($url, '/'));
    }

    /**
     * Get stores
     *
     * @todo temporary method. used until we find a way to convert array to JSON on JS side
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _getStores()
    {
        $stores = $this->getRequest()->getParam('stores');

        $defaultStore = -1;
        $emptyStores = -2;
        if ($stores == $defaultStore) {
            /** @var \Magento\Core\Model\StoreManagerInterface $storeManager */
            $storeManager = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface');
            $ids = array_keys($storeManager->getStores());
            $stores = array(array_shift($ids));
        } elseif ($stores == $emptyStores) {
            $stores = array();
        }

        if (!is_array($stores)) {
            throw new \InvalidArgumentException('Param "stores" is not valid');
        }

        return $stores;
    }
}
