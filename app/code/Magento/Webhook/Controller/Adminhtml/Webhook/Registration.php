<?php

namespace Magento\Webhook\Controller\Adminhtml\Webhook;

/**
 * Registration controller
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
class Registration extends \Magento\Backend\Controller\AbstractAction
{
    const DATA_SUBSCRIPTION_ID = 'subscription_id';
    const DATA_TOPICS = 'topics';
    const DATA_NAME = 'name';

    /** Key used to store subscription data into the registry */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Param keys used to extract subscription details from the Request */
    const PARAM_SUBSCRIPTION_ID = 'id';
    const PARAM_APIKEY = 'apikey';
    const PARAM_APISECRET = 'apisecret';
    const PARAM_EMAIL = 'email';
    const PARAM_COMPANY = 'company';

    /** @var \Magento\Core\Model\Registry */
    private $_registry;

    /** @var \Magento\Webhook\Service\SubscriptionV1Interface */
    private $_subscriptionService;

    /** @var \Magento\Webhook\Model\Webapi\User\Factory */
    private $_userFactory;


    /**
     * @param \Magento\Webhook\Model\Webapi\User\Factory $userFactory
     * @param \Magento\Webhook\Service\SubscriptionV1Interface $subscriptionService
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Backend\Controller\Context $context
     * @param string $areaCode
     */
    public function __construct(
        \Magento\Webhook\Model\Webapi\User\Factory $userFactory,
        \Magento\Webhook\Service\SubscriptionV1Interface $subscriptionService,
        \Magento\Core\Model\Registry $registry,
        \Magento\Backend\Controller\Context $context,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);
        $this->_userFactory = $userFactory;
        $this->_subscriptionService = $subscriptionService;
        $this->_registry = $registry;
    }

    /**
     * Activate subscription
     * Step 1 - display subscription required resources
     */
    public function activateAction()
    {
        try {
            $this->_initSubscription();
            $this->loadLayout();
            $this->renderLayout();
        } catch (\Magento\Core\Exception $e) {
            $this->_redirectFailed($e->getMessage());
        }
    }

    /**
     * Agree to provide required subscription resources
     * Step 2 - redirect to specified auth action
     */
    public function acceptAction()
    {
        try {
            $subscriptionData = $this->_initSubscription();

            $route = '*/webhook_registration/user';
            $this->_redirect(
                $route,
                array(self::PARAM_SUBSCRIPTION_ID => $subscriptionData[self::DATA_SUBSCRIPTION_ID])
            );
        } catch (\Magento\Core\Exception $e) {
            $this->_redirectFailed($e->getMessage());
        }
    }

    /**
     * Displays form for gathering api user data
     */
    public function userAction()
    {
        try {
            $this->_initSubscription();
            $this->loadLayout();
            $this->renderLayout();
        } catch (\Magento\Core\Exception $e) {
            $this->_redirectFailed($e->getMessage());
        }
    }

    /**
     * Continue createApiUser
     */
    public function registerAction()
    {
        try {
            $subscriptionData = $this->_initSubscription();
            /** @var string $key */
            $key = $this->getRequest()->getParam(self::PARAM_APIKEY);
            /** @var string $secret */
            $secret = $this->getRequest()->getParam(self::PARAM_APISECRET);
            /** @var string $email */
            $email = $this->getRequest()->getParam(self::PARAM_EMAIL);
            /** @var string $company */
            $company = $this->getRequest()->getParam(self::PARAM_COMPANY);

            if (empty($key) || empty($secret) || empty($email)) {
                throw new \Magento\Webhook\Exception(
                    __('API Key, API Secret and Contact Email are required fields.')
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->_redirectFailed(__('Invalid Email address provided'));
                return;
            }

            $userContext = array(
                'email' => $email,
                'key'       => $key,
                'secret'    => $secret,
                'company' => $company,
            );

            /** @var string[] $topics */
            $topics = $subscriptionData[self::DATA_TOPICS];
            $userId = $this->_userFactory->createUser($userContext, $topics);

            $subscriptionData['api_user_id'] = $userId;
            $subscriptionData['status'] = \Magento\Webhook\Model\Subscription::STATUS_ACTIVE;
            $subscriptionData = $this->_subscriptionService->update($subscriptionData);

            $this->_redirectSucceeded($subscriptionData);

        } catch (\Magento\Core\Exception $e) {
            $this->_redirectFailed($e->getMessage());
        }
    }

    /**
     * Redirect to this page when the authentication process is completed successfully
     */
    public function succeededAction()
    {
        try {
            $this->loadLayout();
            $this->renderLayout();
            $subscriptionData = $this->_initSubscription();

            $this->_getSession()->addSuccess(
                __('The subscription \'%1\' has been activated.',
                    $subscriptionData[self::DATA_NAME])
            );
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
    }

    /**
     * Redirect to this action when the authentication process fails for any reason.
     */
    public function failedAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Initialize general settings for subscription
     *
     * @throws \Exception|\Magento\Core\Exception if subscription can't be found
     * @return array
     */
    protected function _initSubscription()
    {
        $subscriptionId = (int) $this->getRequest()->getParam(self::PARAM_SUBSCRIPTION_ID);
        $subscriptionData = $this->_subscriptionService->get($subscriptionId);

        $this->_registry->register(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION, $subscriptionData);
        return $subscriptionData;
    }

    /**
     * Log successful subscription and redirect to success page
     *
     * @param array $subscriptionData
     */
    protected function _redirectSucceeded(array $subscriptionData)
    {
        $this->_getSession()->addSuccess(
            __('The subscription \'%1\' has been activated.', $subscriptionData[self::DATA_NAME])
        );
        $this->_redirect('*/webhook_registration/succeeded',
            array(self::PARAM_SUBSCRIPTION_ID => $subscriptionData[self::DATA_SUBSCRIPTION_ID]));
    }

    /**
     * Add error and redirect to failure page
     *
     * @param string $errorMessage
     */
    protected function _redirectFailed($errorMessage)
    {
        $this->_getSession()->addError($errorMessage);
        $this->_redirect('*/webhook_registration/failed');
    }
}
