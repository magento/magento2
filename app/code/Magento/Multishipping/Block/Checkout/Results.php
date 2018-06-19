<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Theme\Block\Html\Title;

/**
 * Multi-shipping checkout results information
 *
 * @api
 */
class Results extends Success
{
    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var Multishipping
     */
    private $multishipping;

    /**
     * @param Context $context
     * @param Multishipping $multishipping
     * @param AddressConfig $addressConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param SessionManagerInterface $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        Multishipping $multishipping,
        AddressConfig $addressConfig,
        OrderRepositoryInterface $orderRepository,
        SessionManagerInterface $session,
        array $data = []
    ) {
        parent::__construct($context, $multishipping, $data);

        $this->multishipping = $multishipping;
        $this->addressConfig = $addressConfig;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
    }

    /**
     * Returns shipping addresses from quote.
     *
     * @return array
     */
    public function getQuoteShippingAddresses(): array
    {
        return $this->multishipping->getQuote()->getAllShippingAddresses();
    }

    /**
     * Returns all failed addresses from quote.
     *
     * @return array
     */
    public function getFailedAddresses(): array
    {
        $addresses = $this->getQuoteShippingAddresses();
        if ($this->getAddressError($this->getQuoteBillingAddress())) {
            $addresses[] = $this->getQuoteBillingAddress();
        }
        return $addresses;
    }

    /**
     * Retrieve order shipping address.
     *
     * @param int $orderId
     * @return OrderAddress|null
     */
    public function getOrderShippingAddress(int $orderId)
    {
        return $this->orderRepository->get($orderId)->getShippingAddress();
    }

    /**
     * Retrieve quote billing address.
     *
     * @return QuoteAddress
     */
    public function getQuoteBillingAddress(): QuoteAddress
    {
        return $this->getCheckout()->getQuote()->getBillingAddress();
    }

    /**
     * Returns formatted shipping address from placed order.
     *
     * @param OrderAddress $address
     * @return string
     */
    public function formatOrderShippingAddress(OrderAddress $address): string
    {
        return $this->getAddressOneline($address->getData());
    }

    /**
     * Returns formatted shipping address from quote.
     *
     * @param QuoteAddress $address
     * @return string
     */
    public function formatQuoteShippingAddress(QuoteAddress $address): string
    {
        return $this->getAddressOneline($address->getData());
    }

    /**
     * Checks if address type is shipping.
     *
     * @param QuoteAddress $address
     * @return bool
     */
    public function isShippingAddress(QuoteAddress $address): bool
    {
        return $address->getAddressType() === QuoteAddress::ADDRESS_TYPE_SHIPPING;
    }

    /**
     * Get unescaped address formatted as one line string.
     *
     * @param array $address
     * @return string
     */
    private function getAddressOneline(array $address): string
    {
        $renderer = $this->addressConfig->getFormatByCode('oneline')->getRenderer();

        return $renderer->renderArray($address);
    }

    /**
     * Returns address error.
     *
     * @param QuoteAddress $address
     * @return string
     */
    public function getAddressError(QuoteAddress $address): string
    {
        $errors = $this->session->getAddressErrors();

        return $errors[$address->getId()] ?? '';
    }

    /**
     * Add title to block head.
     *
     * @throws LocalizedException
     * @return Success
     */
    protected function _prepareLayout(): Success
    {
        /** @var Title $pageTitle */
        $pageTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageTitle) {
            $title = $this->getOrderIds() ? $pageTitle->getPartlySuccessTitle() : $pageTitle->getFailedTitle();
            $pageTitle->setPageTitle($title);
        }

        return parent::_prepareLayout();
    }
}
