<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for validation of customer address form on admin.
 */
class Validate extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $formFactory;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param Share|null $shareConfig
     * @param CustomerRepositoryInterface|null $customerRepository
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        ?Share $shareConfig = null,
        ?CustomerRepositoryInterface $customerRepository = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formFactory = $formFactory;
        $this->shareConfig = $shareConfig
            ?? ObjectManager::getInstance()->get(Share::class);
        $this->customerRepository = $customerRepository
            ?? ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $this->storeManager = $storeManager
            ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * AJAX customer address validation action
     *
     * @return Json
     */
    public function execute(): Json
    {
        /** @var \Magento\Framework\DataObject $response */
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);

        /** @var \Magento\Framework\DataObject $validatedResponse */
        $validatedResponse = $this->validateCustomerAddress($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($validatedResponse->getError()) {
            $validatedResponse->setError(true);
            $validatedResponse->setMessages($response->getMessages());
        }

        $resultJson->setData($validatedResponse);

        return $resultJson;
    }

    /**
     * Customer address validation.
     *
     * @param DataObject $response
     * @return \Magento\Framework\DataObject
     */
    private function validateCustomerAddress(DataObject $response): DataObject
    {
        $addressForm = $this->formFactory->create('customer_address', 'adminhtml_customer_address');
        if ($this->getRequest()->getParam('parent_id')) {
            $customer = $this->customerRepository->getById($this->getRequest()->getParam('parent_id'));
            if (!$this->shareConfig->isGlobalScope() && $customer->getStoreId()) {
                $this->storeManager->setCurrentStore($customer->getStoreId());
            }
        }
        $formData = $addressForm->extractData($this->getRequest());
        $errors = $addressForm->validateData($formData);
        if ($errors !== true) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(true);
        }

        return $response;
    }
}
