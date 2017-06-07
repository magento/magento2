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
 */
class BillingAgreement implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * Paypal data
     *
     * @var Data
     */
    private $paypalData;

    /**
     * @var Config
     */
    private $config;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Escaper
     *
     * @var Escaper
     */
    private $escaper;

    /**
     * Start express action
     *
     * @var string
     */
    private $startAction = 'paypal/express/start/button/1';

    /**
     * @param CurrentCustomer $currentCustomer
     * @param Data $paypalData
     * @param ConfigFactory $paypalConfigFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
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
