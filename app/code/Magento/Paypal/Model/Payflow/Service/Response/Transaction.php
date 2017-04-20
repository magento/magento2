<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Framework\Session\Generic;
use Magento\Paypal\Model\Payflowpro;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class Transaction
 */
class Transaction
{
    /**
     * @var Generic
     */
    protected $sessionTransparent;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Transparent
     */
    protected $transparent;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentManagement;

    /**
     * @var HandlerInterface
     */
    private $errorHandler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Generic $sessionTransparent
     * @param CartRepositoryInterface $quoteRepository
     * @param Transparent $transparent
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param HandlerInterface $errorHandler
     * @param Logger $logger
     */
    public function __construct(
        Generic $sessionTransparent,
        CartRepositoryInterface $quoteRepository,
        Transparent $transparent,
        PaymentMethodManagementInterface $paymentManagement,
        HandlerInterface $errorHandler,
        Logger $logger
    ) {
        $this->sessionTransparent = $sessionTransparent;
        $this->quoteRepository = $quoteRepository;
        $this->transparent = $transparent;
        $this->paymentManagement = $paymentManagement;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
    }

    /**
     * Returns gateway response data object.
     *
     * @param array $gatewayTransactionResponse
     * @return DataObject
     */
    public function getResponseObject($gatewayTransactionResponse)
    {
        $response = new DataObject();
        $response = $this->transparent->mapGatewayResponse((array) $gatewayTransactionResponse, $response);

        $this->logger->debug(
            (array) $gatewayTransactionResponse,
            (array) $this->transparent->getDebugReplacePrivateDataKeys(),
            (bool) $this->transparent->getDebugFlag()
        );

        return $response;
    }

    /**
     * Saves payment information in quote.
     *
     * @param DataObject $response
     * @return void
     * @throws \InvalidArgumentException
     */
    public function savePaymentInQuote($response)
    {
        $quote = $this->quoteRepository->get($this->sessionTransparent->getQuoteId());

        $payment = $this->paymentManagement->get($quote->getId());
        if (!$payment instanceof Payment) {
            throw new \InvalidArgumentException("Variable must contain instance of \\Quote\\Payment.");
        }

        $payment->setData(OrderPaymentInterface::CC_TYPE, $response->getData(OrderPaymentInterface::CC_TYPE));
        $payment->setAdditionalInformation(Payflowpro::PNREF, $response->getData(Payflowpro::PNREF));

        $this->errorHandler->handle($payment, $response);

        $this->paymentManagement->set($quote->getId(), $payment);
    }
}
