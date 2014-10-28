<?php
/**
 *
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
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

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
}
