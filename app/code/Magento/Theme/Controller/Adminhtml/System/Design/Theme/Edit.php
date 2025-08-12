<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class Edit
 * @deprecated 100.2.0
 */
class Edit extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Edit theme
     *
     * @return void
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('id');
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->_objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        try {
            $theme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);
            if ($themeId && (!$theme->load($themeId)->getId() || !$theme->isVisible())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We cannot find theme "%1".', $themeId));
            }
            $this->_coreRegistry->register('current_theme', $theme);

            $this->_view->loadLayout();
            /** @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css $tab */
            $tab = $this->_view->getLayout()->getBlock('theme_edit_tabs_tab_css_tab');
            if ($tab && $tab->canShowTab()) {
                /** @var \Magento\Theme\Helper\Theme $helper */
                $helper = $this->_objectManager->get(\Magento\Theme\Helper\Theme::class);
                $files = $helper->getCssAssets($theme);
                $tab->setFiles($files);
            }
            $this->_setActiveMenu('Magento_Theme::system_design_theme');
            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('adminhtml/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot find the theme.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_redirect('adminhtml/*/');
        }
    }
}
