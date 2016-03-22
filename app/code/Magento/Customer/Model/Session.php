<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as CustomerData;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;

/**
 * Customer session model
 * @method string getNoReferer()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Customer object
     *
     * @var CustomerData
     */
    protected $_customer;

    /**
     * @var ResourceCustomer
     */
    protected $_customerResource;

    /**
     * Customer model
     *
     * @var Customer
     */
    protected $_customerModel;

    /**
     * Flag with customer id validations result
     *
     * @var bool|null
     */
    protected $_isCustomerIdChecked = null;

    /**
     * Customer URL
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data|null
     */
    protected $_coreUrl = null;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var  CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param Share $configShare
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param ResourceCustomer $customerResource
     * @param CustomerFactory $customerFactory
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\App\Response\Http $response
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        Config\Share $configShare,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        \Magento\Customer\Model\Url $customerUrl,
        ResourceCustomer $customerResource,
        CustomerFactory $customerFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerRepositoryInterface $customerRepository,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->_coreUrl = $coreUrl;
        $this->_customerUrl = $customerUrl;
        $this->_configShare = $configShare;
        $this->_customerResource = $customerResource;
        $this->_customerFactory = $customerFactory;
        $this->_urlFactory = $urlFactory;
        $this->_session = $session;
        $this->customerRepository = $customerRepository;
        $this->_eventManager = $eventManager;
        $this->_httpContext = $httpContext;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
        $this->groupManagement = $groupManagement;
        $this->response = $response;
        $this->_eventManager->dispatch('customer_session_init', ['customer_session' => $this]);
    }

    /**
     * Retrieve customer sharing configuration model
     *
     * @return Share
     */
    public function getCustomerConfigShare()
    {
        return $this->_configShare;
    }

    /**
     * Set customer object and setting customer id in session
     *
     * @param   CustomerData $customer
     * @return  $this
     */
    public function setCustomerData(CustomerData $customer)
    {
        $this->_customer = $customer;
        if ($customer === null) {
            $this->setCustomerId(null);
        } else {
            $this->_httpContext->setValue(
                Context::CONTEXT_GROUP,
                $customer->getGroupId(),
                \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID
            );
            $this->setCustomerId($customer->getId());
        }
        return $this;
    }

    /**
     * Retrieve customer model object
     *
     * @return CustomerData
     */
    public function getCustomerData()
    {
        if (!$this->_customer instanceof CustomerData && $this->getCustomerId()) {
            $this->_customer = $this->customerRepository->getById($this->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * Returns Customer data object with the customer information
     *
     * @return CustomerData
     */
    public function getCustomerDataObject()
    {
        /* TODO refactor this after all usages of the setCustomer is refactored */
        return $this->getCustomer()->getDataModel();
    }

    /**
     * Set Customer data object with the customer information
     *
     * @param CustomerData $customerData
     * @return $this
     */
    public function setCustomerDataObject(CustomerData $customerData)
    {
        $this->setId($customerData->getId());
        $this->getCustomer()->updateData($customerData);
        return $this;
    }

    /**
     * Set customer model and the customer id in session
     *
     * @param   Customer $customerModel
     * @return  $this
     * use setCustomerId() instead
     */
    public function setCustomer(Customer $customerModel)
    {
        $this->_customerModel = $customerModel;
        $this->_httpContext->setValue(
            Context::CONTEXT_GROUP,
            $customerModel->getGroupId(),
            \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID
        );
        $this->setCustomerId($customerModel->getId());
        if (!$customerModel->isConfirmationRequired() && $customerModel->getConfirmation()) {
            $customerModel->setConfirmation(null)->save();
        }

        /**
         * The next line is a workaround.
         * It is used to distinguish users that are logged in from user data set via methods similar to setCustomerId()
         */
        $this->unsIsCustomerEmulated();

        return $this;
    }

    /**
     * Retrieve customer model object
     *
     * @return Customer
     * use getCustomerId() instead
     */
    public function getCustomer()
    {
        if ($this->_customerModel === null) {
            $this->_customerModel = $this->_customerFactory->create()->load($this->getCustomerId());
        }

        return $this->_customerModel;
    }

    /**
     * Set customer id
     *
     * @param int|null $id
     * @return $this
     */
    public function setCustomerId($id)
    {
        $this->storage->setData('customer_id', $id);
        return $this;
    }

    /**
     * Retrieve customer id from current session
     *
     * @api
     * @return int|null
     */
    public function getCustomerId()
    {
        if ($this->storage->getData('customer_id')) {
            return $this->storage->getData('customer_id');
        }
        return null;
    }

    /**
     * Retrieve customer id from current session
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getCustomerId();
    }

    /**
     * Set customer id
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setId($customerId)
    {
        return $this->setCustomerId($customerId);
    }

    /**
     * Set customer group id
     *
     * @param int|null $id
     * @return $this
     */
    public function setCustomerGroupId($id)
    {
        $this->storage->setData('customer_group_id', $id);
        return $this;
    }

    /**
     * Get customer group id
     * If customer is not logged in system, 'not logged in' group id will be returned
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->storage->getData('customer_group_id')) {
            return $this->storage->getData('customer_group_id');
        }
        if ($this->getCustomerData()) {
            $customerGroupId = $this->getCustomerData()->getGroupId();
            $this->setCustomerGroupId($customerGroupId);
            return $customerGroupId;
        }
        return Group::NOT_LOGGED_IN_ID;
    }

    /**
     * Checking customer login status
     *
     * @api
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)$this->getCustomerId()
            && $this->checkCustomerId($this->getId())
            && !$this->getIsCustomerEmulated();
    }

    /**
     * Check exists customer (light check)
     *
     * @param int $customerId
     * @return bool
     */
    public function checkCustomerId($customerId)
    {
        if ($this->_isCustomerIdChecked === $customerId) {
            return true;
        }

        try {
            $this->customerRepository->getById($customerId);
            $this->_isCustomerIdChecked = $customerId;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomerAsLoggedIn($customer)
    {
        $this->setCustomer($customer);
        $this->_eventManager->dispatch('customer_login', ['customer' => $customer]);
        $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $this->getCustomerDataObject()]);
        $this->regenerateId();
        return $this;
    }

    /**
     * @param CustomerData $customer
     * @return $this
     */
    public function setCustomerDataAsLoggedIn($customer)
    {
        $this->_httpContext->setValue(Context::CONTEXT_AUTH, true, false);
        $this->setCustomerData($customer);

        $customerModel = $this->_customerFactory->create()->updateData($customer);

        $this->setCustomer($customerModel);

        $this->_eventManager->dispatch('customer_login', ['customer' => $customerModel]);
        $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
        return $this;
    }

    /**
     * Authorization customer by identifier
     *
     * @api
     * @param   int $customerId
     * @return  bool
     */
    public function loginById($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $this->setCustomerDataAsLoggedIn($customer);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Logout customer
     *
     * @api
     * @return $this
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->_eventManager->dispatch('customer_logout', ['customer' => $this->getCustomer()]);
            $this->_logout();
        }
        $this->_httpContext->unsValue(Context::CONTEXT_AUTH);
        return $this;
    }

    /**
     * Authenticate controller action by login customer
     *
     * @param   bool|null $loginUrl
     * @return  bool
     */
    public function authenticate($loginUrl = null)
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $this->setBeforeAuthUrl($this->_createUrl()->getUrl('*/*/*', ['_current' => true]));
        if (isset($loginUrl)) {
            $this->response->setRedirect($loginUrl);
        } else {
            $arguments = $this->_customerUrl->getLoginUrlParams();
            if ($this->_session->getCookieShouldBeReceived() && $this->_createUrl()->getUseSession()) {
                $arguments += [
                    '_query' => [
                        $this->sidResolver->getSessionIdQueryParam($this->_session) => $this->_session->getSessionId(),
                    ]
                ];
            }
            $this->response->setRedirect(
                $this->_createUrl()->getUrl(\Magento\Customer\Model\Url::ROUTE_ACCOUNT_LOGIN, $arguments)
            );
        }

        return false;
    }

    /**
     * Set auth url
     *
     * @param string $key
     * @param string $url
     * @return $this
     */
    protected function _setAuthUrl($key, $url)
    {
        $url = $this->_coreUrl->removeRequestParam($url, $this->sidResolver->getSessionIdQueryParam($this));
        // Add correct session ID to URL if needed
        $url = $this->_createUrl()->getRebuiltUrl($url);
        return $this->storage->setData($key, $url);
    }

    /**
     * Logout without dispatching event
     *
     * @return $this
     */
    protected function _logout()
    {
        $this->_customer = null;
        $this->_customerModel = null;
        $this->setCustomerId(null);
        $this->setCustomerGroupId($this->groupManagement->getNotLoggedInGroup()->getId());
        $this->destroy(['clear_storage' => false]);
        return $this;
    }

    /**
     * Set Before auth url
     *
     * @param string $url
     * @return $this
     */
    public function setBeforeAuthUrl($url)
    {
        return $this->_setAuthUrl('before_auth_url', $url);
    }

    /**
     * Set After auth url
     *
     * @param string $url
     * @return $this
     */
    public function setAfterAuthUrl($url)
    {
        return $this->_setAuthUrl('after_auth_url', $url);
    }

    /**
     * Reset core session hosts after reseting session ID
     *
     * @return $this
     */
    public function regenerateId()
    {
        parent::regenerateId();
        $this->_cleanHosts();
        return $this;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }
}
