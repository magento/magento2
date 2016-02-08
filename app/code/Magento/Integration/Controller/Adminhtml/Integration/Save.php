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
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Integration Save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * @var \Magento\Security\Helper\SecurityCookie
     */
    protected $securityCookieHelper;

    /**
     * @var \Magento\Security\Model\SecurityManager
     */
    protected $securityManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Integration\Api\OauthServiceInterface $oauthService
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Integration\Model\ResourceModel\Integration\Collection $integrationCollection
     * @param \Magento\Security\Helper\SecurityCookie $securityCookieHelper
     * @param \Magento\Security\Model\SecurityManager $securityManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Integration\Model\ResourceModel\Integration\Collection $integrationCollection,
        \Magento\Security\Helper\SecurityCookie $securityCookieHelper,
        \Magento\Security\Model\SecurityManager $securityManager
    ) {
        parent::__construct(
            $context,
            $registry,
            $logger,
            $integrationService,
            $oauthService,
            $jsonHelper,
            $integrationData,
            $escaper,
            $integrationCollection
        );
        $this->securityCookieHelper = $securityCookieHelper;
        $this->securityManager = $securityManager;
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
                $integrationData = $this->getIntegration($integrationId);
                if (!$integrationData) {
                    return;
                }
                if ($integrationData[Info::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG) {
                    throw new LocalizedException(__('Cannot edit integrations created via config file.'));
                }
            }
            $password = $this->getRequest()->getParam(
                \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info::DATA_CONSUMER_PASSWORD
            );
            $user = $this->_auth->getUser();
            $this->securityManager->adminIdentityCheck($user, $password);
            $this->processData($integrationData);
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->securityCookieHelper->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('*');
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setIntegrationData($this->getRequest()->getPostValue());
            $this->redirectOnSaveError();
        } catch (IntegrationException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_getSession()->setIntegrationData($integrationData);
            $this->redirectOnSaveError();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->redirectOnSaveError();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->redirectOnSaveError();
        }
    }

    /**
     * Get Integration entity
     *
     * @param int $integrationId
     * @return \Magento\Integration\Model\Integration|null
     */
    protected function getIntegration($integrationId)
    {
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

        return $integrationData;
    }

    /**
     * Redirect merchant to 'Edit integration' or 'New integration' if error happened during integration save.
     *
     * @return void
     */
    protected function redirectOnSaveError()
    {
        $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            $this->_redirect('*/*/edit', ['id' => $integrationId]);
        } else {
            $this->_redirect('*/*/new');
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
