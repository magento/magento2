<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Checkout;

use Magento\CardinalCommerce\Model\Config;
use Magento\CardinalCommerce\Model\Request\TokenBuilder;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Configuration provider.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var TokenBuilder
     */
    private $requestJwtBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param TokenBuilder $requestJwtBuilder
     * @param Config $config
     */
    public function __construct(
        TokenBuilder $requestJwtBuilder,
        Config $config
    ) {
        $this->requestJwtBuilder = $requestJwtBuilder;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        $config['cardinal'] = [
            'environment' => $this->config->getEnvironment(),
            'requestJWT' => $this->requestJwtBuilder->build()
        ];

        return $config;
    }
}
