<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Framework\Exception\IntegrationException;

/**
 * Class \Magento\Integration\Controller\Adminhtml\Integration\PermissionsDialog
 *
 * @since 2.0.0
 */
class PermissionsDialog extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Show permissions popup.
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            try {
                $integrationData = $this->_integrationService->get($integrationId)->getData();
                $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
            } catch (IntegrationException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError(__('Internal error. Check exception log for details.'));
                $this->_redirect('*/*');
                return;
            }
        } else {
            $this->messageManager->addError(__('Integration ID is not specified or is invalid.'));
            $this->_redirect('*/*/');
            return;
        }

        /** Add handles of the tabs which are defined in other modules */
        $handleNodes = $this->_view->getLayout()->getUpdate()->getFileLayoutUpdatesXml()->xpath(
            '//referenceBlock[@name="integration.activate.permissions.tabs"]/../@id'
        );
        $handles = [];
        if (is_array($handleNodes)) {
            foreach ($handleNodes as $node) {
                $handles[] = (string)$node;
            }
        }
        $this->_view->loadLayout($handles);
        $this->_view->renderLayout();
    }
}
