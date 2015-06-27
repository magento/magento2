<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Edit extends \Magento\Customer\Controller\Account
{
    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var DataObjectHelper */
    protected $dataObjectHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $customerSession, $resultPageFactory);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $block = $resultPage->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->_getSession()->getCustomerFormData(true);
        $customerId = $this->_getSession()->getCustomerId();
        $customerDataObject = $this->customerRepository->getById($customerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray(
                $customerDataObject,
                $data,
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
        }
        $this->_getSession()->setCustomerData($customerDataObject);
        $this->_getSession()->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $resultPage->getConfig()->getTitle()->set(__('Account Information'));
        $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        return $resultPage;
    }
}
