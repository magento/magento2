<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\ShippingRates;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\ResultInterface
     */
    protected $result;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $session
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->checkoutSession = $session;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws Action\NotFoundException
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $address = $quote->getShippingAddress();
        $address->collectShippingRates()->save();
        $rates = $address->getGroupedAllShippingRates();
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setData($rates);
        return $result;
    }
}
