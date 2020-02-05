<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Model\Checkout;

use Magento\AuthorizenetCardinal\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Configuration provider.
 *
 * @deprecated 100.3.1 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        $config['cardinal'] = [
            'isActiveFor' => [
                'authorizenet' => $this->config->isActive()
            ]
        ];

        return $config;
    }
}
