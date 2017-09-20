<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Controller\Button;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\OneTouchOrdering\Model\CustomerAddresses;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;
use Magento\Customer\Model\Session;

class Available extends Action
{
    /**
     * @var OneTouchOrdering
     */
    private $oneTouchOrdering;
    /**
     * @var Session
     */
    private $customerSession;

    private $customerAddresses;

    /**
     * Available constructor.
     * @param Context $context
     * @param OneTouchOrdering $oneTouchOrdering
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        OneTouchOrdering $oneTouchOrdering,
        Session $customerSession,
        CustomerAddresses $customerAddresses
    ) {
        parent::__construct($context);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $available = false;

        /** @var JsonResult $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($this->customerSession->isLoggedIn()) {
            $available = $this->oneTouchOrdering->isAvailableForCustomer($this->customerSession->getCustomer());
        }
        $resultData = [
            'available' => $available
        ];
        if (1) {
            $resultData += [
                'addresses' => $this->customerAddresses->getFormattedAddresses(),
                'defaultAddress' => $this->customerAddresses->getDefaultAddressId()
            ];
        }
        $result->setData($resultData);

        return $result;
    }
}
