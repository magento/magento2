<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Plugin;

use Magento\Checkout\Controller\Index\Index;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\SdkUrl;

/**
 * Modify query params in PayPal SDK Url to enable PayNow experience
 * See https://developer.paypal.com/docs/checkout/integration-features/confirmation-page/
 */
class CheckoutIndex
{
    /**
     * @var SdkUrl
     */
    private $sdkUrl;

    /**
     * @var Config
     */
    private $configFactory;

    /**
     * @param SdkUrl $sdkUrl
     * @param Config $config
     */
    public function __construct(
        SdkUrl $sdkUrl,
        ConfigFactory $config
    ) {
        $this->sdkUrl = $sdkUrl;
        $this->configFactory = $config;
    }

    /**
     * Modify URL query parameter
     *
     * @param Index $subject
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(Index $subject)
    {
        // Check If PP SmartButtons enabled
        $config = $this->configFactory->create()->setMethod(Config::METHOD_EXPRESS);
        if ((bool)(int) $config->getValue('in_context')) {
            $this->sdkUrl->setQueryParam('commit', 'true');
        }

        return null;
    }
}
