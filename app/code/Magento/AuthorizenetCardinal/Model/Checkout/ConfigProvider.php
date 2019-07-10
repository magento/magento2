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
