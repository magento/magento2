<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Block;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Block for representing the payment form
 *
 * @api
 * @since 100.2.1
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class Form extends Cc
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Quote
     */
    private $sessionQuote;

    /**
     * @param Context $context
     * @param PaymentConfig $paymentConfig
     * @param Config $config
     * @param Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        Config $config,
        Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->config = $config;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * Check if cvv validation is available
     *
     * @return boolean
     * @since 100.2.1
     */
    public function isCvvEnabled(): bool
    {
        return $this->config->isCvvEnabled($this->sessionQuote->getStoreId());
    }
}
