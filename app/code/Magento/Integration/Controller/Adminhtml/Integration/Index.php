<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

/**
 * Class \Magento\Integration\Controller\Adminhtml\Integration\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Integrations grid.
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $unsecureIntegrationsCount = $this->_integrationCollection->addUnsecureUrlsFilter()->getSize();
        if ($unsecureIntegrationsCount > 0) {
            // @codingStandardsIgnoreStart
            $this->messageManager->addNotice(__('Warning! Integrations not using HTTPS are insecure and potentially expose private or personally identifiable information')
            // @codingStandardsIgnoreEnd
            );
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('Integrations'), __('Integrations'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Integrations'));
        $this->_view->renderLayout();
    }
}
