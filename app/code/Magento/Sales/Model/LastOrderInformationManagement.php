<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LastOrderInformationManagement implements \Magento\Sales\Api\LastOrderInformationManagementInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * LastOrderInformationManagement constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return mixed
     */
    public function getLastRealOrderId()
    {
        return $this->checkoutSession->getLastRealOrderId();
    }

    /**
     * @return bool|Order
     */
    public function getLastOrderInformation()
    {
        if ($this->getLastRealOrderId()) {
            $order = $this->orderFactory->create()->loadByIncrementId($this->getLastRealOrderId());
            return $order;
        }
        return false;
    }
}
