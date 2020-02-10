<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\DataObject;

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
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formFactory = $formFactory;
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
