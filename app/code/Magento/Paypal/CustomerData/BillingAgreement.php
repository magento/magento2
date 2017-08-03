<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;

/**
 * BillingAgreement section
 * @since 2.1.0
 */
class BillingAgreement implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     * @since 2.1.0
     */
    private $currentCustomer;

    /**
     * Paypal data
     *
     * @var Data
     * @since 2.1.0
     */
    private $paypalData;

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * Url Builder
     *
     * @var UrlInterface
     * @since 2.1.0
     */
    private $urlBuilder;

    /**
     * Escaper
     *
     * @var Escaper
     * @since 2.1.0
     */
    private $escaper;

    /**
     * Start express action
     *
     * @var string
     * @since 2.1.0
     */
    private $startAction = 'paypal/express/start/button/1';

    /**
     * @param CurrentCustomer $currentCustomer
     * @param Data $paypalData
     * @param ConfigFactory $paypalConfigFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @since 2.1.0
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        Data $paypalData,
        ConfigFactory $paypalConfigFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->paypalData = $paypalData;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->config = $paypalConfigFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getSectionData()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        if ($this->paypalData->shouldAskToCreateBillingAgreement($this->config, $customerId)) {
            return [
                'askToCreate' => true,
                'confirmUrl' => $this->escaper->escapeUrl(
                    $this->urlBuilder->getUrl(
                        $this->startAction,
                        [\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT => 1]
                    )
                ),
                'confirmMessage' => $this->escaper->escapeJs(
                    __('Would you like to sign a billing agreement to streamline further purchases with PayPal?')
                )
            ];
        }

        return [];
    }
}
