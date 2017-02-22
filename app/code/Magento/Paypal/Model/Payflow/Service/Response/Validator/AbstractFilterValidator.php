<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Validator;

use Magento\Framework\Session\Generic;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Class AbstractFilterValidator
 */
abstract class AbstractFilterValidator
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Generic
     */
    protected $sessionTransparent;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentManagement;

    /**
     * Constructor
     *
     * @param Generic $sessionTransparent
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentMethodManagementInterface $paymentManagement
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Generic $sessionTransparent,
        PaymentMethodManagementInterface $paymentManagement
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->sessionTransparent = $sessionTransparent;
        $this->paymentManagement = $paymentManagement;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig()
    {
        $quote = $this->quoteRepository->get($this->sessionTransparent->getQuoteId());
        return $this->paymentManagement->get($quote->getId())->getMethodInstance()->getConfigInterface();
    }
}
