<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Message\Error;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Index
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var \Magento\Framework\Validator
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_validator;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     * @since 2.0.0
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_customerFactory = null;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_addressFactory = null;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     * @since 2.0.0
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     * @since 2.0.0
     */
    protected $_formFactory;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $_customerRepository;

    /**
     * @var  \Magento\Customer\Helper\View
     * @since 2.0.0
     */
    protected $_viewHelper;

    /**
     * @var \Magento\Framework\Math\Random
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_random;

    /**
     * @var ObjectFactory
     * @since 2.0.0
     */
    protected $_objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @var Mapper
     * @since 2.0.0
     */
    protected $addressMapper;

    /**
     * @var AccountManagementInterface
     * @since 2.0.0
     */
    protected $customerAccountManagement;

    /**
     * @var AddressRepositoryInterface
     * @since 2.0.0
     */
    protected $addressRepository;

    /**
     * @var CustomerInterfaceFactory
     * @since 2.0.0
     */
    protected $customerDataFactory;

    /**
     * @var AddressInterfaceFactory
     * @since 2.0.0
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     * @since 2.0.0
     */
    protected $customerMapper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     * @since 2.0.0
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     * @since 2.0.0
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $viewHelper
     * @param \Magento\Framework\Math\Random $random
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $customerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        CustomerInterfaceFactory $customerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_formFactory = $formFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_viewHelper = $viewHelper;
        $this->_random = $random;
        $this->_customerRepository = $customerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->addressMapper = $addressMapper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->addressRepository = $addressRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerMapper = $customerMapper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->_objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Customer initialization
     *
     * @return string customer id
     * @since 2.0.0
     */
    protected function initCurrentCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('id');

        if ($customerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }

        return $customerId;
    }

    /**
     * Prepare customer default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     * @since 2.0.0
     */
    protected function prepareDefaultCustomerTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Customers'));
    }

    /**
     * Add errors messages to session.
     *
     * @param array|string $messages
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * Helper function that handles mass actions by taking in a callable for handling a single customer action.
     *
     * @param callable $singleAction A single action callable that takes a customer ID as input
     * @param int[] $customerIds Array of customer Ids to perform the action upon
     * @return int Number of customers successfully acted upon
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected function actUponMultipleCustomers(callable $singleAction, $customerIds)
    {
        if (!is_array($customerIds)) {
            $this->messageManager->addError(__('Please select customer(s).'));
            return 0;
        }
        $customersUpdated = 0;
        foreach ($customerIds as $customerId) {
            try {
                $singleAction($customerId);
                $customersUpdated++;
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $customersUpdated;
    }
}
