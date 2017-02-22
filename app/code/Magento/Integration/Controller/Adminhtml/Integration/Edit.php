<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Framework\Exception\IntegrationException;

class Edit extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Edit integration action.
     *
     * @return void
     */
    public function execute()
    {
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            try {
                $integrationData = $this->_integrationService->get($integrationId)->getData();
                $originalName = $this->escaper->escapeHtml($integrationData[Info::DATA_NAME]);
            } catch (IntegrationException $e) {
                $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError(__('Internal error. Check exception log for details.'));
                $this->_redirect('*/*');
                return;
            }
            $restoredIntegration = $this->_getSession()->getIntegrationData();
            if (isset($restoredIntegration[Info::DATA_ID]) && $integrationId == $restoredIntegration[Info::DATA_ID]) {
                $integrationData = array_merge($integrationData, $restoredIntegration);
            }
        } else {
            $this->messageManager->addError(__('Integration ID is not specified or is invalid.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
        $this->_view->loadLayout();
        $this->_getSession()->setIntegrationData([]);
        $this->_setActiveMenu('Magento_Integration::system_integrations');

        if ($this->_integrationData->isConfigType($integrationData)) {
            $title = __('View "%1" Integration', $originalName);
        } else {
            $title = __('Edit "%1" Integration', $originalName);
        }

        $this->_addBreadcrumb($title, $title);
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }
}
