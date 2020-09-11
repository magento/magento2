<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Paypal\Model\Payflowpro;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Process PayPal transaction response.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transaction
{
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
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Transparent $transparent
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param HandlerInterface $errorHandler
     * @param Logger $logger
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Transparent $transparent,
        PaymentMethodManagementInterface $paymentManagement,
        HandlerInterface $errorHandler,
        Logger $logger,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->transparent = $transparent;
        $this->paymentManagement = $paymentManagement;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
        $this->dateTimeFactory = $dateTimeFactory;
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
            ['PayPal PayflowPro response:' => (array)$gatewayTransactionResponse],
            (array) $this->transparent->getDebugReplacePrivateDataKeys(),
            (bool) $this->transparent->getDebugFlag()
        );

        return $response;
    }

    /**
     * Saves payment information in quote.
     *
     * @param DataObject $response
     * @param int $cartId
     * @return void
     * @throws \InvalidArgumentException
     */
    public function savePaymentInQuote($response, $cartId)
    {
        $quote = $this->quoteRepository->get($cartId);
        $payment = $this->paymentManagement->get($quote->getId());
        if (!$payment instanceof Payment) {
            throw new \InvalidArgumentException("Variable must contain instance of \\Quote\\Payment.");
        }

        $payment->setData(OrderPaymentInterface::CC_TYPE, $response->getData(OrderPaymentInterface::CC_TYPE));
        $payment->setAdditionalInformation(Payflowpro::PNREF, $response->getData(Payflowpro::PNREF));
        $payment->setAdditionalInformation('result_code', $response->getData('result'));

        $expDate = $response->getData('expdate');
        $expMonth = $this->getCcExpMonth($expDate);
        $payment->setCcExpMonth($expMonth);
        $expYear = $this->getCcExpYear($expDate);
        $payment->setCcExpYear($expYear);

        $this->errorHandler->handle($payment, $response);

        $this->paymentManagement->set($quote->getId(), $payment);
    }

    /**
     * Extracts expiration month from PayPal response expiration date.
     *
     * @param string $expDate format {MMYY}
     * @return int
     */
    private function getCcExpMonth(string $expDate): int
    {
        return (int)substr($expDate, 0, 2);
    }

    /**
     * Extracts expiration year from PayPal response expiration date.
     *
     * @param string $expDate format {MMYY}
     * @return int
     */
    private function getCcExpYear(string $expDate): int
    {
        $last2YearDigits = (int)substr($expDate, 2, 2);
        $currentDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $first2YearDigits = (int)substr($currentDate->format('Y'), 0, 2);

        // case when credit card expires at next century
        if ((int)$currentDate->format('y') > $last2YearDigits) {
            $first2YearDigits++;
        }

        return 100 * $first2YearDigits + $last2YearDigits;
    }
}
