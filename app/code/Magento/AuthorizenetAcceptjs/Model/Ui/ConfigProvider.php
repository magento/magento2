<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Model\Ui;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Retrieves config needed for checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * @param Config $config
     * @param CartInterface $cart
     */
    public function __construct(Config $config, CartInterface $cart)
    {
        $this->config = $config;
        $this->cart = $cart;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->cart->getStoreId();

        return [
            'payment' => [
                Config::METHOD => [
                    'clientKey' => $this->config->getClientKey($storeId),
                    'apiLoginID' => $this->config->getLoginId($storeId),
                    'environment' => $this->config->getEnvironment($storeId),
                    'useCvv' => $this->config->isCvvEnabled($storeId),
                ]
            ]
        ];
    }
}
