<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Configuration;

/**
 * Parse configuration for a specific logging channel.
 *
 * Channel specific configuration is dependend on the global logging configuration
 * from LogConfigurationProviderInterface
 */
class ChannelConfigurationParser implements ChannelConfigurationParserInterface
{
    /**
     * @var LogConfigurationProviderInterface
     */
    private $logConfigProvider;

    /**
     * @param LogConfigurationProviderInterface $logConfigProvider
     */
    public function __construct(LogConfigurationProviderInterface $logConfigProvider)
    {
        $this->logConfigProvider = $logConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function parseConfiguration(array $channelConfig): ParsedChannelConfiguration
    {
        $handlers = [];
        $processors = [];

        if (isset($channelConfig['handlers'])) {
            foreach ($channelConfig['handlers'] as $key) {
                $handlers[] = $this->logConfigProvider->getHandlerByKey($key);
            }
        }

        if (isset($channelConfig['processors'])) {
            foreach ($channelConfig['processors'] as $key) {
                $processors[] = $this->logConfigProvider->getProcessorByKey($key);
            }
        }

        return new ParsedChannelConfiguration($handlers, $processors);
    }
}
