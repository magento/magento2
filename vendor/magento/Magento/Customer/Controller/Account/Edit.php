<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class Edit extends \Magento\Customer\Controller\Account
{
    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var CustomerDataBuilder */
    protected $customerBuilder;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerDataBuilder $customerBuilder
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerDataBuilder $customerBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerBuilder = $customerBuilder;
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
            $customerDataObject = $this->customerBuilder->mergeDataObjectWithArray($customerDataObject, $data)
                ->create();
        }
        $this->_getSession()->setCustomerData($customerDataObject);
        $this->_getSession()->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $this->_view->getPage()->getConfig()->getTitle()->set(__('Account Information'));
        $this->_view->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->_view->renderLayout();
    }
}
