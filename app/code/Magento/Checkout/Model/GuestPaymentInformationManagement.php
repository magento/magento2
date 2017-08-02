<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class GuestPaymentInformationManagement implements \Magento\Checkout\Api\GuestPaymentInformationManagementInterface
{

    /**
     * @var \Magento\Quote\Api\GuestBillingAddressManagementInterface
     * @since 2.0.0
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\GuestPaymentMethodManagementInterface
     * @since 2.0.0
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     * @since 2.0.0
     */
    protected $cartManagement;

    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     * @since 2.0.0
     */
    protected $paymentInformationManagement;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     * @since 2.0.0
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     * @since 2.0.0
     */
    protected $cartRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
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
        return $orderId;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function savePaymentInformation(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {
            $billingAddress->setEmail($email);
            $this->billingAddressManagement->assign($cartId, $billingAddress);
        } else {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $this->cartRepository->getActive($quoteIdMask->getQuoteId())->getBillingAddress()->setEmail($email);
        }

        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
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
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getLogger()
    {
        if (!$this->logger) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }
}
