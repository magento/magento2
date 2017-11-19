<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger;

use Magento\Framework\Logger\Configuration\ChannelConfigurationParserInterface;
use Monolog\Logger;

/**
 * Monolog instance specifically configured with a specific channel name and configuration
 * Configuration is provided trough ChannelConfigurationParserInterface
 *
 * You should not instantiate this class directly. The preferred method is to include an instance of
 * Magento\Framework\Logger\...Logchannel where the ... stand for the specific channel name.
 * An example of this could be \Magento\Framework\Logger\MainLogchannel
 *
 * This class is used from \Magento\Framework\Logger\Code\Generator\Logchannel to generate specific Logchannel
 * instances.
 */
class Logchannel extends Logger
{
    /**
     * @var string
     */
    protected $channelName = 'main';

    /**
     * @var array
     */
    protected $channelConfiguration = [];

    /**
     * @param ChannelConfigurationParserInterface $channelConfigParser
     */
    public function __construct(ChannelConfigurationParserInterface $channelConfigParser)
    {
        $parsedConfiguration = $channelConfigParser->parseConfiguration($this->channelConfiguration);
        parent::__construct(
            $this->channelName,
            $parsedConfiguration->getHandlers(),
            $parsedConfiguration->getProcessors()
        );
    }

    /**
     * Adds a log record.
     *
     * @param integer $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [])
    {
        /**
         * To preserve compatibility with Exception messages.
         * And support PSR-3 context standard.
         *
         * @link http://www.php-fig.org/psr/psr-3/#context PSR-3 context standard
         */
        if ($message instanceof \Exception && !isset($context['exception'])) {
            $context['exception'] = $message;
        }

        $message = $message instanceof \Exception ? $message->getMessage() : $message;

        return parent::addRecord($level, $message, $context);
    }
}
