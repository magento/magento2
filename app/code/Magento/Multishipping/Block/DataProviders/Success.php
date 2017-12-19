<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Block\DataProviders;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * Provides additional data for multishipping checkout success step.
 */
class Success implements ArgumentInterface
{
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var Multishipping
     */
    private $multishipping;

    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @param SessionManagerInterface $session
     * @param Multishipping $multishipping
     * @param AddressConfig $addressConfig
     */
    public function __construct(
        SessionManagerInterface $session,
        Multishipping $multishipping,
        AddressConfig $addressConfig
    ) {
        $this->session = $session;
        $this->multishipping = $multishipping;
        $this->addressConfig = $addressConfig;
    }

    /**
     * Returns shipping addresses from quote.
     *
     * @return array
     */
    public function getShippingAddresses(): array
    {
        return $this->multishipping->getQuote()->getAllShippingAddresses();
    }

    /**
     * Returns quote.
     *
     * @return Quote
     */
    public function getQuote(): Quote
    {
        return $this->multishipping->getQuote();
    }

    /**
     * Returns address error.
     *
     * @param Address $address
     * @return string
     */
    public function getAddressError(Address $address): string
    {
        $errors = $this->session->getAddressErrors();

        return $errors[$address->getId()] ?? '';
    }

    /**
     * Get address formatted as html string.
     *
     * @param Address $address
     * @return string
     */
    public function getAddressHtml(Address $address): string
    {
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();

        return $renderer->renderArray($address->getData());
    }
}
