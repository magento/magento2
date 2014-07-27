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
