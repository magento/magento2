<?php
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Mage_Webhook_Adminhtml_Webhook_RegistrationController extends Mage_Backend_Controller_ActionAbstract
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

    /** @var Mage_Core_Model_Registry */
    private $_registry;

    /** @var Mage_Webhook_Service_SubscriptionV1Interface */
    private $_subscriptionService;

    /** @var Mage_Webhook_Model_Webapi_User_Factory */
    private $_userFactory;


    /**
     * @param Mage_Webhook_Model_Webapi_User_Factory $userFactory
     * @param Mage_Webhook_Service_SubscriptionV1Interface $subscriptionService
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Backend_Controller_Context $context
     * @param string $areaCode
     */
    public function __construct(
        Mage_Webhook_Model_Webapi_User_Factory $userFactory,
        Mage_Webhook_Service_SubscriptionV1Interface $subscriptionService,
        Mage_Core_Model_Registry $registry,
        Mage_Backend_Controller_Context $context,
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
        } catch (Mage_Core_Exception $e) {
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
        } catch (Mage_Core_Exception $e) {
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
        } catch (Mage_Core_Exception $e) {
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
                throw new Mage_Webhook_Exception(
                    $this->__('API Key, API Secret and Contact Email are required fields.')
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->_redirectFailed($this->__('Invalid Email address provided'));
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
            $subscriptionData['status'] = Mage_Webhook_Model_Subscription::STATUS_ACTIVE;
            $subscriptionData = $this->_subscriptionService->update($subscriptionData);

            $this->_redirectSucceeded($subscriptionData);

        } catch (Mage_Core_Exception $e) {
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
                $this->__('The subscription \'%s\' has been activated.',
                    $subscriptionData[self::DATA_NAME])
            );
        } catch (Mage_Core_Exception $e) {
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
     * @throws Exception|Mage_Core_Exception if subscription can't be found
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
            $this->__('The subscription \'%s\' has been activated.', $subscriptionData[self::DATA_NAME])
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
