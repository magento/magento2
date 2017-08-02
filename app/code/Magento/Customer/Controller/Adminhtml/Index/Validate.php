<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Message\Error;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Index\Validate
 *
 */
class Validate extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer validation
     *
     * @param \Magento\Framework\DataObject $response
     * @return CustomerInterface|null
     */
    protected function _validateCustomer($response)
    {
        $customer = null;
        $errors = [];

        try {
            /** @var CustomerInterface $customer */
            $customer = $this->customerDataFactory->create();

            $customerForm = $this->_formFactory->create(
                'customer',
                'adminhtml_customer',
                [],
                true
            );
            $customerForm->setInvisibleIgnored(true);

            $data = $customerForm->extractData($this->getRequest(), 'customer');

            if ($customer->getWebsiteId()) {
                unset($data['website_id']);
            }

            $this->dataObjectHelper->populateWithArray(
                $customer,
                $data,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $submittedData = $this->getRequest()->getParam('customer');
            if (isset($submittedData['entity_id'])) {
                $entity_id = $submittedData['entity_id'];
                $customer->setId($entity_id);
            }
            $errors = $this->customerAccountManagement->validate($customer)->getMessages();
        } catch (\Magento\Framework\Validator\Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR) as $error) {
                $errors[] = $error->getText();
            }
        }

        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }

        return $customer;
    }

    /**
     * Customer address validation.
     *
     * @param \Magento\Framework\DataObject $response
     * @return void
     */
    protected function _validateCustomerAddress($response)
    {
        $addresses = $this->getRequest()->getPost('address');
        if (!is_array($addresses)) {
            return;
        }
        foreach (array_keys($addresses) as $index) {
            if ($index == '_template_') {
                continue;
            }

            $addressForm = $this->_formFactory->create('customer_address', 'adminhtml_customer_address');

            $requestScope = sprintf('address/%s', $index);
            $formData = $addressForm->extractData($this->getRequest(), $requestScope);

            $errors = $addressForm->validateData($formData);
            if ($errors !== true) {
                $messages = $response->hasMessages() ? $response->getMessages() : [];
                foreach ($errors as $error) {
                    $messages[] = $error;
                }
                $response->setMessages($messages);
                $response->setError(1);
            }
        }
    }

    /**
     * AJAX customer validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $customer = $this->_validateCustomer($response);
        if ($customer) {
            $this->_validateCustomerAddress($response);
        }
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
