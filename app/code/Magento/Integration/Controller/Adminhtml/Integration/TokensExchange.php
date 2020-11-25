<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;

class TokensExchange extends Integration implements HttpPostActionInterface
{
    /**
     * Let the admin know that integration has been sent for activation and token exchange is in process.
     *
     * @param bool   $isReauthorize
     * @param string $integrationName
     * @return void
     */
    protected function _setActivationInProcessMsg($isReauthorize, $integrationName)
    {
        $integrationName = $this->escaper->escapeHtml($integrationName);
        $msg = $isReauthorize ? __(
            "Integration '%1' has been sent for re-authorization.",
            $integrationName
        ) : __(
            "Integration '%1' has been sent for activation.",
            $integrationName
        );
        $this->messageManager->addNotice($msg);
    }

    /**
     * Post consumer credentials for Oauth integration.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
            $isReauthorize = (bool)$this->getRequest()->getParam(self::PARAM_REAUTHORIZE, 0);
            $integration = $this->_integrationService->get($integrationId);
            if ($isReauthorize) {
                /** Remove existing token associated with consumer before issuing a new one. */
                $this->_oauthService->deleteIntegrationToken($integration->getConsumerId());
                $integration->setStatus(IntegrationModel::STATUS_INACTIVE)->save();
            }
            //Integration chooses to use Oauth for token exchange
            $this->_oauthService->postToConsumer($integration->getConsumerId(), $integration->getEndpoint());
            /** Generate JS popup content */
            $this->_view->loadLayout(false);
            // Activation or authorization is done only when the Oauth token exchange completes
            $this->_setActivationInProcessMsg($isReauthorize, $integration->getName());
            $this->_view->renderLayout();
            $popupContent = $this->_response->getBody();
            $consumer = $this->_oauthService->loadConsumer($integration->getConsumerId());
            if (!$consumer->getId()) {
                throw new \Magento\Framework\Oauth\Exception(
                    __(
                        'A consumer with "%1" ID doesn\'t exist. Verify the ID and try again.',
                        $integration->getConsumerId()
                    )
                );
            }
            /** Initialize response body */
            $result = [
                IntegrationModel::IDENTITY_LINK_URL => $integration->getIdentityLinkUrl(),
                'oauth_consumer_key' => $consumer->getKey(),
                'popup_content' => $popupContent,
            ];
            $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*');
            return;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addErrorMessage(__('Internal error. Check exception log for details.'));
            $this->_redirect('*/*');
            return;
        }
    }
}
