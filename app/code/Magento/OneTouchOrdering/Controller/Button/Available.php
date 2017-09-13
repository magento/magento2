<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Controller\Button;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\OneTouchOrdering\Model\CustomerAddresses;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;
use Magento\OneTouchOrdering\Helper\Data as OneTouchOrderingHelper;

class Available extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OneTouchOrdering
     */
    protected $oneTouchOrdering;
    /**
     * @var CustomerAddresses
     */
    protected $customerAddresses;
    /**
     * @var OneTouchOrderingHelper
     */
    private $oneTouchOrderingHelper;

    public function __construct(
        Context $context,
        OneTouchOrdering $oneTouchOrdering,
        CustomerAddresses $customerAddresses,
        OneTouchOrderingHelper $oneTouchOrderingHelper
    ) {
        parent::__construct($context);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->customerAddresses = $customerAddresses;
        $this->oneTouchOrderingHelper = $oneTouchOrderingHelper;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $resultData = [
            'available' => $this->oneTouchOrdering->isOneTouchOrderingAvailable(),
        ];

        if ($this->oneTouchOrderingHelper->isSelectAddressEnabled()) {
            $resultData += [
                'addresses' => $this->customerAddresses->getFormattedAddresses(),
                'defaultAddress' => $this->customerAddresses->getDefaultAddressId()
            ];
        }

        $result->setData($resultData);

        return $result;
    }
}
