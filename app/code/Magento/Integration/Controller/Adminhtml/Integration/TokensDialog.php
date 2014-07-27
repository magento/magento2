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

class TokensDialog extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Set success message based on Integration activation or re-authorization.
     *
     * @param boolean $isReauthorize Is a re-authorization flow
     * @param string $integrationName Integration name
     * @return void
     */
    protected function _setActivationSuccessMsg($isReauthorize, $integrationName)
    {
        $integrationName = $this->escaper->escapeHtml($integrationName);
        $successMsg = $isReauthorize ? __(
            "The integration '%1' has been re-authorized.",
            $integrationName
        ) : __(
            "The integration '%1' has been activated.",
            $integrationName
        );
        $this->messageManager->addSuccess($successMsg);
    }

    /**
     * Show tokens popup for simple tokens
     *
     * @return void
     */
    public function execute()
    {
        try {
            $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
            $integration = $this->_integrationService->get($integrationId);
            $clearExistingToken = (int)$this->getRequest()->getParam(self::PARAM_REAUTHORIZE, 0);
            if ($this->_oauthService->createAccessToken($integration->getConsumerId(), $clearExistingToken)) {
                $integration->setStatus(IntegrationModel::STATUS_ACTIVE)->save();
            }
            // Important to call get() once again - that will pull newly generated token
            $this->_registry->register(
                self::REGISTRY_KEY_CURRENT_INTEGRATION,
                $this->_integrationService->get($integrationId)->getData()
            );
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
        $this->_view->loadLayout(false);
        //This cannot precede loadlayout(false) else the messages will be removed
        $this->_setActivationSuccessMsg($clearExistingToken, $integration->getName());
        $this->_view->renderLayout();
    }
}
