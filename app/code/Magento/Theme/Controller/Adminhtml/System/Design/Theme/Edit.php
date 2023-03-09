<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use Magento\Theme\Helper\Theme as ThemeHelper;
use Psr\Log\LoggerInterface;

/**
 * Class Edit
 * @deprecated 100.2.0
 */
class Edit extends Theme
{
    /**
     * Edit theme
     *
     * @return void
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('id');
        /** @var ThemeInterface $theme */
        $theme = $this->_objectManager->create(ThemeInterface::class);
        try {
            $theme->setType(ThemeInterface::TYPE_VIRTUAL);
            if ($themeId && (!$theme->load($themeId)->getId() || !$theme->isVisible())) {
                throw new LocalizedException(__('We cannot find theme "%1".', $themeId));
            }
            $this->_coreRegistry->register('current_theme', $theme);

            $this->_view->loadLayout();
            /** @var Css $tab */
            $tab = $this->_view->getLayout()->getBlock('theme_edit_tabs_tab_css_tab');
            if ($tab && $tab->canShowTab()) {
                /** @var ThemeHelper $helper */
                $helper = $this->_objectManager->get(ThemeHelper::class);
                $files = $helper->getCssAssets($theme);
                $tab->setFiles($files);
            }
            $this->_setActiveMenu('Magento_Theme::system_design_theme');
            $this->_view->renderLayout();
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('adminhtml/*/');
        } catch (Exception $e) {
            $this->messageManager->addError(__('We cannot find the theme.'));
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $this->_redirect('adminhtml/*/');
        }
    }
}
