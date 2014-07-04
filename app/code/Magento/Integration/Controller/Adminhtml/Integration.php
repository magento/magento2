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
namespace Magento\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Exception as IntegrationException;
use Magento\Integration\Service\V1\OauthInterface as IntegrationOauthService;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Controller for integrations management.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Integration extends Action
{
    /** Param Key for extracting integration id from Request */
    const PARAM_INTEGRATION_ID = 'id';

    /** Reauthorize flag is used to distinguish activation from reauthorization */
    const PARAM_REAUTHORIZE = 'reauthorize';

    const REGISTRY_KEY_CURRENT_INTEGRATION = 'current_integration';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /** @var \Magento\Framework\Logger */
    protected $_logger;

    /** @var \Magento\Integration\Service\V1\IntegrationInterface */
    private $_integrationService;

    /** @var IntegrationOauthService */
    protected $_oauthService;

    /** @var \Magento\Core\Helper\Data */
    protected $_coreHelper;

    /** @var \Magento\Integration\Helper\Data */
    protected $_integrationData;

    /** @var  \Magento\Integration\Model\Resource\Integration\Collection */
    protected $_integrationCollection;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Integration\Service\V1\IntegrationInterface $integrationService
     * @param IntegrationOauthService $oauthService
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Integration\Model\Resource\Integration\Collection $integrationCollection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Logger $logger,
        \Magento\Integration\Service\V1\IntegrationInterface $integrationService,
        IntegrationOauthService $oauthService,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Integration\Model\Resource\Integration\Collection $integrationCollection
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->_integrationService = $integrationService;
        $this->_oauthService = $oauthService;
        $this->_coreHelper = $coreHelper;
        $this->_integrationData = $integrationData;
        $this->escaper = $escaper;
        $this->_integrationCollection = $integrationCollection;
        parent::__construct($context);
    }

    /**
     * Integrations grid.
     *
     * @return void
     */
    public function indexAction()
    {
        $unsecureEndpointsCount = $this->_integrationCollection->addUnsecureEndpointFilter()->getSize();
        if ($unsecureEndpointsCount > 0) {
            // @codingStandardsIgnoreStart
            $this->messageManager->addNotice(__('Warning! Integrations not using HTTPS are insecure and potentially expose private or personally identifiable information')
            // @codingStandardsIgnoreEnd
            );
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('Integrations'), __('Integrations'));
        $this->_title->add(__('Integrations'));
        $this->_view->renderLayout();
    }

    /**
     * AJAX integrations grid.
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Check ACL.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Integration::integrations');
    }

    /**
     * New integration action.
     *
     * @return void
     */
    public function newAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('New Integration'), __('New Integration'));
        $this->_title->add(__('New Integration'));
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $restoredIntegration = $this->_getSession()->getIntegrationData();
        if ($restoredIntegration) {
            $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $restoredIntegration);
            $this->_getSession()->setIntegrationData(array());
        }
        $this->_view->renderLayout();
    }

    /**
     * Edit integration action.
     *
     * @return void
     */
    public function editAction()
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
                $this->_logger->logException($e);
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
        $this->_getSession()->setIntegrationData(array());
        $this->_setActiveMenu('Magento_Integration::system_integrations');

        if ($this->_integrationData->isConfigType($integrationData)) {
            $title = __('View "%1" Integration', $originalName);
        } else {
            $title = __('Edit "%1" Integration', $originalName);
        }

        $this->_addBreadcrumb($title, $title);
        $this->_title->add($title);
        $this->_view->renderLayout();
    }

    /**
     * Save integration action.
     *
     * @return void
     * @todo: Fix cyclomatic complexity.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveAction()
    {
        /** @var array $integrationData */
        $integrationData = array();
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
                    $this->_logger->logException($e);
                    $this->messageManager->addError(__('Internal error. Check exception log for details.'));
                    $this->_redirect('*/*');
                    return;
                }
            }
            /** @var array $data */
            $data = $this->getRequest()->getPost();
            if (!empty($data)) {
                // TODO: Move out work with API permissions to Web API module
                if (!isset($data['resource'])) {
                    $integrationData['resource'] = array();
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
                }
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $isTokenExchange = $integration->getEndpoint() && $integration->getIdentityLinkUrl() ? '1' : '0';
                    $this->getResponse()->representJson(
                        $this->_coreHelper->jsonEncode(
                            array('integrationId' => $integration->getId(), 'isTokenExchange' => $isTokenExchange)
                        )
                    );
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addError(__('The integration was not saved.'));
            }
        } catch (IntegrationException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_getSession()->setIntegrationData($integrationData);
            $this->_redirectOnSaveError();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        }
    }

    /**
     * Show permissions popup.
     *
     * @return void
     */
    public function permissionsDialogAction()
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
                $this->_logger->logException($e);
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
        $handles = array();
        if (is_array($handleNodes)) {
            foreach ($handleNodes as $node) {
                $handles[] = (string)$node;
            }
        }
        $this->_view->loadLayout($handles);
        $this->_view->renderLayout();
    }

    /**
     * Delete the integration.
     *
     * @return void
     */
    public function deleteAction()
    {
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        try {
            if ($integrationId) {
                $integrationData = $this->_integrationService->get($integrationId);
                if ($this->_integrationData->isConfigType($integrationData)) {
                    $this->messageManager->addError(
                        __(
                            "Uninstall the extension to remove integration '%1'.",
                            $this->escaper->escapeHtml($integrationData[Info::DATA_NAME])
                        )
                    );
                    $this->_redirect('*/*/');
                    return;
                }
                $integrationData = $this->_integrationService->delete($integrationId);
                if (!$integrationData[Info::DATA_ID]) {
                    $this->messageManager->addError(__('This integration no longer exists.'));
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
                $this->messageManager->addError(__('Integration ID is not specified or is invalid.'));
            }
        } catch (\Magento\Integration\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Show tokens popup for simple tokens
     *
     * @return void
     */
    public function tokensDialogAction()
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

    /**
     * Post consumer credentials for Oauth integration.
     *
     * @return void
     */
    public function tokensExchangeAction()
    {
        try {
            $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
            $isReauthorize = (bool)$this->getRequest()->getParam(self::PARAM_REAUTHORIZE, 0);
            $integration = $this->_integrationService->get($integrationId);
            if ($isReauthorize) {
                /** Remove existing token associated with consumer before issuing a new one. */
                $this->_oauthService->deleteToken($integration->getConsumerId());
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

    /**
     * Close window after callback has succeeded
     *
     * @return void
     */
    public function loginSuccessCallbackAction()
    {
        $this->getResponse()->setBody('<script type="text/javascript">setTimeout("self.close()",1000);</script>');
    }

    /**
     * Redirect merchant to 'Edit integration' or 'New integration' if error happened during integration save.
     *
     * @return void
     */
    protected function _redirectOnSaveError()
    {
        $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            $this->_redirect('*/*/edit', array('id' => $integrationId));
        } else {
            $this->_redirect('*/*/new');
        }
    }

    /**
     * Don't actually redirect if we've got AJAX request - return redirect URL instead.
     *
     * @param string $path
     * @param array $arguments
     * @return $this|\Magento\Backend\App\AbstractAction
     */
    protected function _redirect($path, $arguments = array())
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->representJson(
                $this->_coreHelper->jsonEncode(array('_redirect' => $this->getUrl($path, $arguments)))
            );
            return $this;
        } else {
            return parent::_redirect($path, $arguments);
        }
    }

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
     * Let the admin know that activation was failed.
     *
     * @param bool   $isReauthorize
     * @param string $integrationName
     * @return void
     */
    protected function _setActivationFailedMsg($isReauthorize, $integrationName)
    {
        $msg = $isReauthorize ? __(
            "Integration '%1' re-authorization has been failed.",
            $integrationName
        ) : __(
            "Integration '%1' activation has been failed.",
            $integrationName
        );
        $this->messageManager->addError($msg);
    }

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
}
