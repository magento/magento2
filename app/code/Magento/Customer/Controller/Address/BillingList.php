<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Address;

use Magento\Framework\App\Action\Context;

class BillingList extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $addressService;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultFactory = $resultFactory;
        $this->addressService = $addressService;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Customer addresses list
     *
     * @return void
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            $addressList = $this->addressService->getAddresses($this->customerSession->getCustomerId());
        } else {
            $addressList = [];
        }
        $response = $this->resultFactory->create('json');
        $addressList = array_map(
            function($item) {
                return $item->__toArray();
            },
            $addressList
        );
        $response->setData($addressList);
        return $response;
    }
}
