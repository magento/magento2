<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use \Magento\Customer\Service\V1\Data\Customer;
use Magento\Framework\Message\Error;

class Validate extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer validation
     *
     * @param \Magento\Framework\Object $response
     * @return Customer|null
     */
    protected function _validateCustomer($response)
    {
        $customer = null;
        $errors = null;

        try {
            /** @var Customer $customer */
            $customer = $this->_customerBuilder->create();

            $customerForm = $this->_formFactory->create(
                'customer',
                'adminhtml_customer',
                \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customer),
                true
            );
            $customerForm->setInvisibleIgnored(true);

            $data = $customerForm->extractData($this->getRequest(), 'account');

            if ($customer->getWebsiteId()) {
                unset($data['website_id']);
            }

            $customer = $this->_customerBuilder->populateWithArray($data)->create();
            $errors = $this->_customerAccountService->validateCustomerData($customer);
        } catch (\Magento\Framework\Model\Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR) as $error) {
                $errors[] = $error->getText();
            }
        }

        if (!$errors->isValid()) {
            foreach ($errors->getMessages() as $error) {
                $this->messageManager->addError($error);
            }
            $response->setError(1);
        }

        return $customer;
    }

    /**
     * Customer address validation.
     *
     * @param \Magento\Framework\Object $response
     * @return void
     */
    protected function _validateCustomerAddress($response)
    {
        $addressesData = $this->getRequest()->getParam('address');
        if (is_array($addressesData)) {
            foreach (array_keys($addressesData) as $index) {
                if ($index == '_template_') {
                    continue;
                }

                $addressForm = $this->_formFactory->create('customer_address', 'adminhtml_customer_address');

                $requestScope = sprintf('address/%s', $index);
                $formData = $addressForm->extractData($this->getRequest(), $requestScope);

                $errors = $addressForm->validateData($formData);
                if ($errors !== true) {
                    foreach ($errors as $error) {
                        $this->messageManager->addError($error);
                    }
                    $response->setError(1);
                }
            }
        }
    }

    /**
     * AJAX customer validation action
     *
     * @return void
     */
    public function execute()
    {
        $response = new \Magento\Framework\Object();
        $response->setError(0);

        $customer = $this->_validateCustomer($response);
        if ($customer) {
            $this->_validateCustomerAddress($response);
        }

        if ($response->getError()) {
            $this->_view->getLayout()->initMessages();
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
