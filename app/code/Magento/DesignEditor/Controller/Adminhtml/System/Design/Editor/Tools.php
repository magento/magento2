<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

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
