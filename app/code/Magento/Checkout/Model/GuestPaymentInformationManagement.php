<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Model\Quote;

/**
 * Guest payment information management model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestPaymentInformationManagement implements \Magento\Checkout\Api\GuestPaymentInformationManagementInterface
{

    /**
     * @var \Magento\Quote\Api\GuestBillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\GuestPaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     */
    protected $paymentInformationManagement;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
<<<<<<< HEAD
    private $connectionPull;
=======
    private $connectionPool;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
<<<<<<< HEAD
     * @param ResourceConnection|null
=======
     * @param ResourceConnection $connectionPool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
<<<<<<< HEAD
        ResourceConnection $connectionPull = null
=======
        ResourceConnection $connectionPool = null
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
<<<<<<< HEAD
        $this->connectionPull = $connectionPull ?: ObjectManager::getInstance()->get(ResourceConnection::class);
=======
        $this->connectionPool = $connectionPool ?: ObjectManager::getInstance()->get(ResourceConnection::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
<<<<<<< HEAD
        $salesConnection = $this->connectionPull->getConnection('sales');
        $checkoutConnection = $this->connectionPull->getConnection('checkout');
=======
        $salesConnection = $this->connectionPool->getConnection('sales');
        $checkoutConnection = $this->connectionPool->getConnection('checkout');
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $salesConnection->beginTransaction();
        $checkoutConnection->beginTransaction();

        try {
            $this->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
            try {
                $orderId = $this->cartManagement->placeOrder($cartId);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                throw new CouldNotSaveException(
                    __($e->getMessage()),
                    $e
                );
            } catch (\Exception $e) {
                $this->getLogger()->critical($e);
                throw new CouldNotSaveException(
                    __('An error occurred on the server. Please try to place the order again.'),
                    $e
                );
            }
            $salesConnection->commit();
            $checkoutConnection->commit();
        } catch (\Exception $e) {
            $salesConnection->rollBack();
            $checkoutConnection->rollBack();
            throw $e;
        }
<<<<<<< HEAD
        
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $orderId;
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformation(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

        if ($billingAddress) {
            $billingAddress->setEmail($email);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
        } else {
            $quote->getBillingAddress()->setEmail($email);
        }
        $this->limitShippingCarrier($quote);

        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentInformation($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->paymentInformationManagement->getPaymentInformation($quoteIdMask->getQuoteId());
    }

    /**
     * Get logger instance
     *
     * @return \Psr\Log\LoggerInterface
     * @deprecated 100.2.0
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Limits shipping rates request by carrier from shipping address.
     *
     * @param Quote $quote
     *
     * @return void
     * @see \Magento\Shipping\Model\Shipping::collectRates
     */
<<<<<<< HEAD
    private function limitShippingCarrier(Quote $quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getShippingMethod()) {
            $shippingDataArray = explode('_', $shippingAddress->getShippingMethod());
            $shippingCarrier = array_shift($shippingDataArray);
            $shippingAddress->setLimitCarrier($shippingCarrier);
=======
    private function limitShippingCarrier(Quote $quote) : void
    {
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getShippingMethod()) {
            $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
            $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
    }
}
