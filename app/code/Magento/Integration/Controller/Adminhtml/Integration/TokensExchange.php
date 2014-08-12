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
namespace Magento\Integration\Controller\Adminhtml\Integration;

use \Magento\Integration\Model\Integration as IntegrationModel;

class TokensExchange extends \Magento\Integration\Controller\Adminhtml\Integration
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
            /** Initialize response body */
            $result = array(
                IntegrationModel::IDENTITY_LINK_URL => $integration->getIdentityLinkUrl(),
                IntegrationModel::CONSUMER_ID => $integration->getConsumerId(),
                'popup_content' => $popupContent
            );
            $this->getResponse()->representJson($this->_coreHelper->jsonEncode($result));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*');
            return;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            $this->messageManager->addError(__('Internal error. Check exception log for details.'));
            $this->_redirect('*/*');
            return;
        }
    }
}
