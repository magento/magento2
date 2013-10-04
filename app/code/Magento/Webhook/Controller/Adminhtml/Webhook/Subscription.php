<?php
namespace Magento\Webhook\Controller\Adminhtml\Webhook;

/**
 * Subscription controller
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Subscription extends \Magento\Backend\Controller\AbstractAction
{
    /** Param Key for extracting subscription id from Request */
    const PARAM_SUBSCRIPTION_ID = 'id';

    /** Data keys for extracting information from Subscription data array */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';
    const DATA_ALIAS = 'alias';
    const DATA_NAME = 'name';
    const DATA_ENDPOINT_URL = 'endpoint_url';
    const DATA_TOPICS = 'topics';

    /** Keys used for registering data into the registry */
    const REGISTRY_KEY_WEBHOOK_ACTION = 'webhook_action';
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Value stored under the key REGISTRY_KEY_WEBHOOK_ACTION to indicate that this is a new subscription */
    const ACTION_NEW = 'new';

    /** @var \Magento\Core\Model\Registry  */
    private $_registry;

    /** @var \Magento\Webhook\Service\SubscriptionV1Interface */
    private $_subscriptionService;

    /**
     * Class constructor
     *
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Webhook\Service\SubscriptionV1Interface $subscriptionService
     * @param \Magento\Backend\Controller\Context $context
     * @param string $areaCode
     */
    public function __construct(
        \Magento\Core\Model\Registry $registry,
        \Magento\Webhook\Service\SubscriptionV1Interface $subscriptionService,
        \Magento\Backend\Controller\Context $context,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);

        $this->_registry = $registry;
        $this->_subscriptionService = $subscriptionService;
    }

    /**
     * Loads and renders subscription controller layout
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_Webhook::system_api_webapi_webhook')
            ->_title(__('System'))
            ->_title(__('Web Services'))
            ->_title(__('WebHook Subscriptions'));

        $this->renderLayout();
    }

    /**
     * Register new action and throw control to 'edit' action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Initialize subscription and render action layout
     */
    public function editAction()
    {
        try {
            $subscriptionData  = $this->_initSubscriptionData();

            if ($this->_registry->registry(self::REGISTRY_KEY_WEBHOOK_ACTION) !== self::ACTION_NEW) {
                $data = $this->_session->getFormData(true);
                if (!empty($data)) {
                    $subscriptionData = $this->_updateSubscriptionData($subscriptionData, $data);
                }
                $this->_registry->unregister(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);
                $this->_registry->register(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION, $subscriptionData);
            }

            $this->loadLayout()
                ->_setActiveMenu('Magento_Webapi::system_webapi')
                ->_title(__('System'))
                ->_title(__('Web Services'))
                ->_title(__('WebHook Subscriptions'));
            if ($this->_registry->registry(self::REGISTRY_KEY_WEBHOOK_ACTION) === self::ACTION_NEW) {
                $this->_title(__('Add Subscription'));
            } else {
                $this->_title(__('Edit Subscription'));
            }

            $this->renderLayout();
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save subscription action
     */
    public function saveAction()
    {
        try {
            /** @var array $data */
            $data = $this->getRequest()->getPost();
            $subscriptionData = $this->_initSubscriptionData();
            if ($data) {
                $subscriptionData = $this->_updateSubscriptionData($subscriptionData, $data);
                if ($this->_registry->registry(self::REGISTRY_KEY_WEBHOOK_ACTION) === self::ACTION_NEW) {
                    $this->_subscriptionService->create($subscriptionData);
                } else if (
                    isset($subscriptionData[self::DATA_SUBSCRIPTION_ID])
                    && $subscriptionData[self::DATA_SUBSCRIPTION_ID]
                ) {
                    $this->_subscriptionService->update($subscriptionData);
                } else {
                    $this->_subscriptionService->create($subscriptionData);
                }
                $this->_getSession()->addSuccess(
                    __('The subscription \'%1\' has been saved.',
                    $subscriptionData[self::DATA_NAME])
                );
                $this->_redirect('*/*/');
            } else {
                $this->_getSession()->addError(
                    __('The subscription \'%1\' has not been saved, as no data was provided.',
                    $subscriptionData[self::DATA_NAME])
                );
                $this->_redirect(
                    '*/*/edit',
                    array(self::PARAM_SUBSCRIPTION_ID => $this->getRequest()->getParam(self::PARAM_SUBSCRIPTION_ID))
                );
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    /**
     * Delete subscription action
     */
    public function deleteAction()
    {
        try {
            $subscriptionData = $this->_initSubscriptionData();
            if ($this->_isCreatedByUser($subscriptionData)) {
                try {
                    $subscriptionId = isset($subscriptionData[self::DATA_SUBSCRIPTION_ID])
                        ? $subscriptionData[self::DATA_SUBSCRIPTION_ID]
                        : null;
                    $this->_subscriptionService->delete($subscriptionId);
                    $this->_getSession()->addSuccess(
                        __('The subscription \'%1\' has been removed.',
                        $subscriptionData[self::DATA_NAME])
                    );
                }
                catch (\Magento\Core\Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            } else {
                $this->_getSession()->addError(
                    __('The subscription \'%1\' can not be removed.',
                    $subscriptionData[self::DATA_NAME])
                );
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Revoke subscription
     */
    public function revokeAction()
    {
        try {
            $subscriptionId = $this->getRequest()->getParam(self::PARAM_SUBSCRIPTION_ID);
            if ($subscriptionId) {
                $subscriptionData = $this->_subscriptionService->revoke($subscriptionId);
                $this->_getSession()->addSuccess(
                    __('The subscription \'%1\' has been revoked.',
                    $subscriptionData[self::DATA_NAME])
                );
            } else {
                $this->_getSession()->addError(__('No Subscription ID was provided with the request.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/webhook_subscription/index');
    }

    /**
     * Activate subscription. Step 1 - display subscription required resources
     */
    public function activateAction()
    {
        try {
            $subscriptionId = $this->getRequest()->getParam(self::PARAM_SUBSCRIPTION_ID);
            if ($subscriptionId) {
                $subscriptionData = $this->_subscriptionService->activate($subscriptionId);
                $this->_getSession()->addSuccess(
                    __('The subscription \'%1\' has been activated.',
                        $subscriptionData[self::DATA_NAME])
                );
            } else {
                $this->_getSession()->addError(__('No Subscription ID was provided with the request.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/webhook_subscription/index');
    }

    /**
     * Initialize general settings for subscription
     *
     * @return array
     * @throws \Magento\Webhook\Exception
     */
    protected function _initSubscriptionData()
    {
        $subscriptionId = (int) $this->getRequest()->getParam(self::PARAM_SUBSCRIPTION_ID);
        if ($subscriptionId) {
            $subscriptionData = $this->_subscriptionService->get($subscriptionId);
        } else {
            $subscriptionData = array();
            $this->_registry->register(self::REGISTRY_KEY_WEBHOOK_ACTION, self::ACTION_NEW);
        }

        $this->_registry->register(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION, $subscriptionData);
        return $subscriptionData;
    }

    /**
     * Helper function that returns updated subscription data with data gathered from a Form post.
     *
     * We need to make sure that only authorized data is being updated.  For example we disable the 'Version' field
     * in the UI for subscriptions generated by config, we don't want a user to be able to bypass this by performing
     * a manual POST.
     *
     * @param array $subscriptionData
     * @param array $data
     * @return array
     */
    protected function _updateSubscriptionData($subscriptionData, $data)
    {
        return array_merge($subscriptionData, $data);
    }

    /**
     * Determine if a subscription was created by a user or not, by looking at the data.
     *
     * @param array $subscriptionData
     * @return bool true if the subscription was created by a user
     */
    protected function _isCreatedByUser($subscriptionData)
    {
        return !isset($subscriptionData[self::DATA_ALIAS]);
    }
}
