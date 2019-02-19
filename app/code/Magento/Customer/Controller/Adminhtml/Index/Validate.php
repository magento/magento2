<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Message\Error;
use Magento\Customer\Controller\Adminhtml\Index as CustomerAction;

/**
 * Class for validation of customer
 */
class Validate extends CustomerAction implements HttpPostActionInterface, HttpGetActionInterface
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
     * AJAX customer validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $this->_validateCustomer($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
