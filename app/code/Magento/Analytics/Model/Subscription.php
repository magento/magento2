<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;


use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;

class Subscription
{
    /**
     * Path to subscription config setting Enabled.
     *
     * @var string
     */
    private $enabledConfigPath = 'analytics/subscription/enabled';

    /**
     * Resource for storing store configuration values.
     *
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @param ConfigInterface $resourceConfig
     */
    public function __construct(ConfigInterface $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Set subscription enabled config value.
     *
     * @return void
     */
    public function enable()
    {
        $this->resourceConfig
            ->saveConfig(
                $this->enabledConfigPath,
                1,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            );
    }
}
