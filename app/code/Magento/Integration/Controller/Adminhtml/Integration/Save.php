<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\Integration as IntegrationModel;

class Save extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Redirect merchant to 'Edit integration' or 'New integration' if error happened during integration save.
     *
     * @return void
     */
    protected function _redirectOnSaveError()
    {
        $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            $this->_redirect('*/*/edit', ['id' => $integrationId]);
        } else {
            $this->_redirect('*/*/new');
        }
    }

    /**
     * Save integration action.
     *
     * @return void
     */
    public function execute()
    {
        /** @var array $integrationData */
        $integrationData = [];
        try {
            $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
            if ($integrationId) {
                try {
                    $integrationData = $this->_integrationService->get($integrationId)->getData();
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
                if ($integrationData[Info::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG) {
                    throw new LocalizedException(__('Cannot edit integrations created via config file.'));
                }
            }
            $this->processData($integrationData);
        } catch (IntegrationException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_getSession()->setIntegrationData($integrationData);
            $this->_redirectOnSaveError();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        }
    }

    /**
     * Save integration data.
     *
     * @param array $integrationData
     * @return void
     */
    private function processData($integrationData)
    {
        /** @var array $data */
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            if (!isset($data['resource'])) {
                $integrationData['resource'] = [];
            }
            $integrationData = array_merge($integrationData, $data);
            if (!isset($integrationData[Info::DATA_ID])) {
                $integration = $this->_integrationService->create($integrationData);
            } else {
                $integration = $this->_integrationService->update($integrationData);
            }
            if (!$this->getRequest()->isXmlHttpRequest()) {
                $this->messageManager->addSuccess(
                    __(
                        'The integration \'%1\' has been saved.',
                        $this->escaper->escapeHtml($integration->getName())
                    )
                );
                $this->_redirect('*/*/');
            } else {
                $isTokenExchange = $integration->getEndpoint() && $integration->getIdentityLinkUrl() ? '1' : '0';
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(
                        ['integrationId' => $integration->getId(), 'isTokenExchange' => $isTokenExchange]
                    )
                );
            }
        } else {
            $this->messageManager->addError(__('The integration was not saved.'));
        }
    }
}
