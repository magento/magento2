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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model;

use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Resource\Customer as ResourceCustomer;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\Data\Customer as CustomerData;

/**
 * Customer session model
 */
class Session extends \Magento\Session\SessionManager
{
    /**
     * Customer object
     *
     * @var CustomerData
     */
    protected $_customer;

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
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data|null
     */
    protected $_customerData = null;

    /**
     * Core url
     *
     * @var \Magento\Core\Helper\Url|null
     */
    protected $_coreUrl = null;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /** @var  CustomerAccountServiceInterface */
    protected $_customerAccountService;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Core\Model\Store\StorageInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    protected $_converter;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Session\ValidatorInterface $validator
     * @param \Magento\Session\StorageInterface $storage
     * @param Share $configShare
     * @param \Magento\Core\Helper\Url $coreUrl
     * @param \Magento\Customer\Helper\Data $customerData
     * @param ResourceCustomer $customerResource
     * @param CustomerFactory $customerFactory
     * @param \Magento\UrlFactory $urlFactory
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\Http\Context $httpContext
     * @param Converter $converter
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param null $sessionName
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Session\SaveHandlerInterface $saveHandler,
        \Magento\Session\ValidatorInterface $validator,
        \Magento\Session\StorageInterface $storage,
        Config\Share $configShare,
        \Magento\Core\Helper\Url $coreUrl,
        \Magento\Customer\Helper\Data $customerData,
        Resource\Customer $customerResource,
        CustomerFactory $customerFactory,
        \Magento\UrlFactory $urlFactory,
        \Magento\Core\Model\Session $session,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\Http\Context $httpContext,
        \Magento\Customer\Model\Converter $converter,
        CustomerAccountServiceInterface $customerAccountService,
        $sessionName = null
    ) {
        $this->_coreUrl = $coreUrl;
        $this->_customerData = $customerData;
        $this->_configShare = $configShare;
        $this->_customerResource = $customerResource;
        $this->_customerFactory = $customerFactory;
        $this->_urlFactory = $urlFactory;
        $this->_session = $session;
        $this->_customerAccountService = $customerAccountService;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_httpContext = $httpContext;
        parent::__construct($request, $sidResolver, $sessionConfig, $saveHandler, $validator, $storage);
        $this->start($sessionName);
        $this->_converter = $converter;
        $this->_eventManager->dispatch('customer_session_init', array('customer_session' => $this));
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
                \Magento\Customer\Helper\Data::CONTEXT_GROUP,
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
     * @deprecated
     * @return CustomerData
     */
    public function getCustomerData()
    {
        if (!$this->_customer instanceof CustomerData && $this->getCustomerId()) {
            $this->_customer = $this->_customerAccountService->getCustomer($this->getCustomerId());
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
        return $this->_converter->createCustomerFromModel($this->getCustomer());
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
        $this->_converter->updateCustomerModel($this->getCustomer(), $customerData);
        return $this;
    }

    /**
     * Set customer model and the customer id in session
     *
     * @param   Customer $customerModel
     * @return  $this
     * @deprecated use setCustomerId() instead
     */
    public function setCustomer(Customer $customerModel)
    {
        $this->_customerModel = $customerModel;
        $this->_httpContext->setValue(
            \Magento\Customer\Helper\Data::CONTEXT_GROUP,
            $customerModel->getGroupId(),
            \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID
        );
        $this->setCustomerId($customerModel->getId());
        if (!$customerModel->isConfirmationRequired() && $customerModel->getConfirmation()) {
            $customerModel->setConfirmation(null)->save();
        }

        return $this;
    }

    /**
     * Retrieve customer model object
     *
     * @return Customer
     * @deprecated use getCustomerId() instead
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
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)$this->getCustomerId() && (bool)$this->checkCustomerId($this->getId());
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
            $this->_customerAccountService->getCustomer($customerId);
            $this->_isCustomerIdChecked = $customerId;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Customer authorization
     *
     * @param   string $username
     * @param   string $password
     * @return  bool
     */
    public function login($username, $password)
    {
        try {
            $customer = $this->_customerAccountService->authenticate($username, $password);
            $this->setCustomerDataAsLoggedIn($customer);
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
        $this->_eventManager->dispatch('customer_login', array('customer' => $customer));
        $this->regenerateId();
        return $this;
    }

    /**
     * @param CustomerData $customer
     * @return $this
     */
    public function setCustomerDataAsLoggedIn($customer)
    {
        $this->_httpContext->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, true, false);
        $this->setCustomerData($customer);

        $customerModel = $this->_converter->createCustomerModel($customer);
        $this->setCustomer($customerModel);

        $this->_eventManager->dispatch('customer_login', array('customer' => $customerModel));
        return $this;
    }

    /**
     * Authorization customer by identifier
     *
     * @param   int $customerId
     * @return  bool
     */
    public function loginById($customerId)
    {
        try {
            $customer = $this->_customerAccountService->getCustomer($customerId);
            $this->setCustomerDataAsLoggedIn($customer);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Logout customer
     *
     * @return $this
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->_eventManager->dispatch('customer_logout', array('customer' => $this->getCustomer()));
            $this->_logout();
        }
        $this->_httpContext->unsValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH);
        return $this;
    }

    /**
     * Authenticate controller action by login customer
     *
     * @param   \Magento\App\Action\Action $action
     * @param   bool|null $loginUrl
     * @return  bool
     */
    public function authenticate(\Magento\App\Action\Action $action, $loginUrl = null)
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $this->setBeforeAuthUrl($this->_createUrl()->getUrl('*/*/*', array('_current' => true)));
        if (isset($loginUrl)) {
            $action->getResponse()->setRedirect($loginUrl);
        } else {
            $arguments = $this->_customerData->getLoginUrlParams();
            if ($this->_session->getCookieShouldBeReceived() && $this->_createUrl()->getUseSession()) {
                $arguments += array(
                    '_query' => array(
                        $this->sidResolver->getSessionIdQueryParam($this->_session) => $this->_session->getSessionId()
                    )
                );
            }
            $action->getResponse()->setRedirect(
                $this->_createUrl()->getUrl(\Magento\Customer\Helper\Data::ROUTE_ACCOUNT_LOGIN, $arguments)
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
        $this->setCustomerGroupId(\Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID);
        $this->destroy(array('clear_storage' => false));
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
     * @param bool $deleteOldSession
     * @return $this
     */
    public function regenerateId($deleteOldSession = true)
    {
        parent::regenerateId($deleteOldSession);
        $this->_cleanHosts();
        return $this;
    }

    /**
     * @return \Magento\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }
}
