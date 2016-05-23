<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();

        $customerData = [];
        $customerData['account'] = [];
        $customerData['address'] = [];
        $customer = null;
        $isExistingCustomer = (bool)$customerId;
        if ($isExistingCustomer) {
            try {
                $customer = $this->_customerRepository->getById($customerId);
                $customerData['account'] = $this->customerMapper->toFlatArray($customer);
                $customerData['account'][CustomerInterface::ID] = $customerId;
                try {
                    $addresses = $customer->getAddresses();
                    foreach ($addresses as $address) {
                        $customerData['address'][$address->getId()] = $this->addressMapper->toFlatArray($address);
                        $customerData['address'][$address->getId()]['id'] = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    //do nothing
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while editing the customer.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/*/index');
                return $resultRedirect;
            }
        }
        $customerData['customer_id'] = $customerId;
        $this->_getSession()->setCustomerData($customerData);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Customer::customer_manage');
        $this->prepareDefaultCustomerTitle($resultPage);
        $resultPage->setActiveMenu('Magento_Customer::customer');
        if ($isExistingCustomer) {
            $resultPage->getConfig()->getTitle()->prepend($this->_viewHelper->getCustomerName($customer));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Customer'));
        }
        return $resultPage;
    }
}
