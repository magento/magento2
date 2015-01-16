<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

use Magento\Framework\Model\Exception as CoreException;
use Magento\Framework\View\Design\ThemeInterface;

class Launch extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Pass data to the Tools panel blocks that is needed it for rendering
     *
     * @param ThemeInterface $theme
     * @param string $mode
     * @return $this
     */
    protected function _configureToolsBlocks($theme, $mode)
    {
        /** @var $toolsBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Tools */
        $toolsBlock = $this->_view->getLayout()->getBlock('design_editor_tools');
        if ($toolsBlock) {
            $toolsBlock->setMode($mode);
        }

        /** @var $cssTabBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Css */
        $cssTabBlock = $this->_view->getLayout()->getBlock('design_editor_tools_code_css');
        if ($cssTabBlock) {
            /** @var $helper \Magento\Core\Helper\Theme */
            $helper = $this->_objectManager->get('Magento\Core\Helper\Theme');
            $assets = $helper->getCssAssets($theme);
            $cssTabBlock->setAssets($assets)
                ->setThemeId($theme->getId());
        }
        return $this;
    }

    /**
     * Set to iframe block selected mode and theme
     *
     * @param ThemeInterface $editableTheme
     * @param string $mode
     * @return $this
     */
    protected function _configureEditorBlock($editableTheme, $mode)
    {
        /** @var $editorBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Container */
        $editorBlock = $this->_view->getLayout()->getBlock('design_editor');
        $currentUrl = $this->_getCurrentUrl($editableTheme->getId(), $mode);
        $editorBlock->setFrameUrl($currentUrl);
        $editorBlock->setTheme($editableTheme);

        return $this;
    }

    /**
     * Pass data to the Toolbar panel blocks that is needed for rendering
     *
     * @param ThemeInterface $theme
     * @param ThemeInterface $editableTheme
     * @param string $mode
     * @return $this
     */
    protected function _configureToolbarBlocks($theme, $editableTheme, $mode)
    {
        /** @var $toolbarBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons */
        $toolbarBlock = $this->_view->getLayout()->getBlock('design_editor_toolbar_buttons');
        $toolbarBlock->setThemeId($editableTheme->getId())->setVirtualThemeId($theme->getId())->setMode($mode);

        /** @var $saveButtonBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save */
        $saveButtonBlock = $this->_view->getLayout()->getBlock('design_editor_toolbar_buttons_save');
        if ($saveButtonBlock) {
            $saveButtonBlock->setTheme(
                $theme
            )->setMode(
                $mode
            )->setHasThemeAssigned(
                $this->_customizationConfig->hasThemeAssigned()
            );
        }
        /** @var $saveButtonBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Edit */
        $editButtonBlock = $this->_view->getLayout()->getBlock('design_editor_toolbar_buttons_edit');
        if ($editButtonBlock) {
            $editButtonBlock->setTheme($editableTheme);
        }

        return $this;
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
        $vdeUrlModel = $this->_objectManager->create(
            'Magento\DesignEditor\Model\Url\NavigationMode',
            ['data' => ['mode' => $mode, 'themeId' => $themeId]]
        );
        $url = $this->_getSession()->getData(\Magento\DesignEditor\Model\State::CURRENT_URL_SESSION_KEY);
        if (empty($url)) {
            $url = '';
        }
        return $vdeUrlModel->getUrl(ltrim($url, '/'));
    }

    /**
     * Activate the design editor in the session and redirect to the frontend of the selected store
     *
     * @return void
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $mode = (string)$this->getRequest()->getParam('mode', \Magento\DesignEditor\Model\State::MODE_NAVIGATION);
        try {
            /** @var \Magento\DesignEditor\Model\Theme\Context $themeContext */
            $themeContext = $this->_objectManager->get('Magento\DesignEditor\Model\Theme\Context');
            $themeContext->setEditableThemeById($themeId);
            $launchedTheme = $themeContext->getEditableTheme();
            if ($launchedTheme->isPhysical()) {
                $launchedTheme = $launchedTheme->getDomainModel(
                    ThemeInterface::TYPE_PHYSICAL
                )->createVirtualTheme(
                    $launchedTheme
                );
                $this->_redirect($this->getUrl('adminhtml/*/*', ['theme_id' => $launchedTheme->getId()]));
                return;
            }
            $editableTheme = $themeContext->getStagingTheme();

            $this->_eventManager->dispatch('design_editor_activate');

            $this->_setTitle();
            $this->_view->loadLayout();

            $this->_configureToolbarBlocks($launchedTheme, $editableTheme, $mode);
            //top panel
            $this->_configureToolsBlocks($launchedTheme, $mode);
            //bottom panel
            $this->_configureEditorBlock($launchedTheme, $mode);
            //editor container

            /** @var $storeViewBlock \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\StoreView */
            $storeViewBlock = $this->_view->getLayout()->getBlock('theme.selector.storeview');
            $storeViewBlock->setData(['actionOnAssign' => 'none', 'theme_id' => $launchedTheme->getId()]);

            $this->_view->renderLayout();
        } catch (CoreException $e) {
            $this->messageManager->addException($e, $e->getMessage());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('adminhtml/*/');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Sorry, there was an unknown error.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('adminhtml/*/');
            return;
        }
    }
}
