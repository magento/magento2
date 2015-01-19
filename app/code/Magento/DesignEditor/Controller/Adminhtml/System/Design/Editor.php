<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design;

use Magento\Framework\Model\Exception as CoreException;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Store\Model\Store;

/**
 * Backend controller for the design editor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Editor extends \Magento\Backend\App\Action
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Theme\Model\Config $themeConfig
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Theme\Model\Config $themeConfig,
        \Magento\Theme\Model\Config\Customization $customizationConfig
    ) {
        $this->_themeConfig = $themeConfig;
        $this->_customizationConfig = $customizationConfig;
        parent::__construct($context);
    }

    /**
     * Set page title
     *
     * @return void
     */
    protected function _setTitle()
    {
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Store Designer'));
    }

    /**
     * Load theme by id
     *
     * @param int $themeId
     * @return \Magento\Core\Model\Theme
     * @throws CoreException
     */
    protected function _loadThemeById($themeId)
    {
        /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->create('Magento\Framework\View\Design\Theme\FlyweightFactory');
        $theme = $themeFactory->create($themeId);
        if (empty($theme)) {
            throw new CoreException(__('We can\'t find this theme.'));
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
     * Check whether is customized themes in database
     *
     * @return bool
     */
    protected function _isFirstEntrance()
    {
        $isCustomized = (bool)$this->_objectManager->get(
            'Magento\Core\Model\Resource\Theme\CollectionFactory'
        )->create()->addTypeFilter(
            ThemeInterface::TYPE_VIRTUAL
        )->getSize();
        return !$isCustomized;
    }

    /**
     * Load layout
     *
     * @return void
     */
    protected function _renderStoreDesigner()
    {
        try {
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_DesignEditor::system_design_editor');
            $this->_setTitle();
            if (!$this->_isFirstEntrance()) {
                /** @var $assignedThemeBlock
                 * \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Assigned */
                $assignedThemeBlock = $this->_view->getLayout()->getBlock('assigned.theme.list');
                $assignedThemeBlock->setCollection($this->_customizationConfig->getAssignedThemeCustomizations());

                /** @var $unassignedThemeBlock
                 * \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Unassigned */
                $unassignedThemeBlock = $this->_view->getLayout()->getBlock('unassigned.theme.list');
                $unassignedThemeBlock->setCollection($this->_customizationConfig->getUnassignedThemeCustomizations());
                $unassignedThemeBlock->setHasThemeAssigned($this->_customizationConfig->hasThemeAssigned());
            }
            /** @var $storeViewBlock \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\StoreView */
            $storeViewBlock = $this->_view->getLayout()->getBlock('theme.selector.storeview');
            $storeViewBlock->setData('actionOnAssign', 'refresh');
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t load the list of themes.'));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
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
        }

        return false;
    }
}
