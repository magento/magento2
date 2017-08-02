<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

/**
 * Class \Magento\Integration\Controller\Adminhtml\Integration\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * New integration action.
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->restoreResourceAndSaveToRegistry();
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('New Integration'), __('New Integration'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Integration'));
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $restoredIntegration = $this->_getSession()->getIntegrationData();
        if ($restoredIntegration) {
            $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $restoredIntegration);
            $this->_getSession()->setIntegrationData([]);
        }
        $this->_view->renderLayout();
    }
}
