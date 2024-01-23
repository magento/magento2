<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Integration\Controller\Adminhtml\Integration implements HttpPostActionInterface
{
    /**
     * Delete the integration.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        try {
            if ($integrationId) {
                $integrationData = $this->_integrationService->get($integrationId);
                if ($this->_integrationData->isConfigType($integrationData)) {
                    $this->messageManager->addErrorMessage(
                        __(
                            "Uninstall the extension to remove integration '%1'.",
                            $this->escaper->escapeHtml($integrationData[Info::DATA_NAME])
                        )
                    );
                    return $resultRedirect->setPath('*/*/');
                }
                $integrationData = $this->_integrationService->delete($integrationId);
                if (!$integrationData[Info::DATA_ID]) {
                    $this->messageManager->addErrorMessage(__('This integration no longer exists.'));
                } else {
                    //Integration deleted successfully, now safe to delete the associated consumer data
                    if (isset($integrationData[Info::DATA_CONSUMER_ID])) {
                        $this->_oauthService->deleteConsumer($integrationData[Info::DATA_CONSUMER_ID]);
                    }
                    $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
                    $this->messageManager->addSuccess(
                        __(
                            "The integration '%1' has been deleted.",
                            $this->escaper->escapeHtml($integrationData[Info::DATA_NAME])
                        )
                    );
                }
            } else {
                $this->messageManager->addErrorMessage(__('Integration ID is not specified or is invalid.'));
            }
        } catch (IntegrationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
