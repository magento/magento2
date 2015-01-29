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
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $customerSession);
    }

    /**
     * Forgot customer account information page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $block = $this->_view->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->_getSession()->getCustomerFormData(true);
        $customerId = $this->_getSession()->getCustomerId();
        $customerDataObject = $this->customerRepository->getById($customerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray($customerDataObject, $data);
        }
        $this->_getSession()->setCustomerData($customerDataObject);
        $this->_getSession()->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $this->_view->getPage()->getConfig()->getTitle()->set(__('Account Information'));
        $this->_view->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->_view->renderLayout();
    }
}
