<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Configuration;

/**
 * Parse channel specific configuration from deployment configuration. This does not
 * include the global logging configuration, that is handled by LogConfigurationProviderInterface
 */
interface ChannelConfigurationParserInterface
{
    /**
     * Get Configured Handlers
     *
     * @param array $channelConfig
     * @return ParsedChannelConfiguration
     */
    public function parseConfiguration(array $channelConfig): ParsedChannelConfiguration;
}
