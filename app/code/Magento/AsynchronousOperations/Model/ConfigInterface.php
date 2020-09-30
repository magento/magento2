<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;

/**
 * Class for accessing to Webapi_Async configuration.
 *
 * @api
 * @since 100.2.3
 */
interface ConfigInterface
{
    /**#@+
     * Constants for Webapi Asynchronous Config generation
     */
    const CACHE_ID = 'webapi_async_config';
    const TOPIC_PREFIX = 'async.';
    const DEFAULT_CONSUMER_INSTANCE = MassConsumer::class;
    const DEFAULT_CONSUMER_CONNECTION = 'amqp';
    const DEFAULT_CONSUMER_MAX_MESSAGE = null;
    const SERVICE_PARAM_KEY_INTERFACE = 'interface';
    const SERVICE_PARAM_KEY_METHOD = 'method';
    const SERVICE_PARAM_KEY_TOPIC = 'topic';
    const DEFAULT_HANDLER_NAME = 'async';
    const SYSTEM_TOPIC_NAME = 'async.system.required.wrapper.topic';
    const SYSTEM_TOPIC_CONFIGURATION =  [
        CommunicationConfig::TOPIC_NAME           => self::SYSTEM_TOPIC_NAME,
        CommunicationConfig::TOPIC_IS_SYNCHRONOUS => false,
        CommunicationConfig::TOPIC_REQUEST        => OperationInterface::class,
        CommunicationConfig::TOPIC_REQUEST_TYPE   => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
        CommunicationConfig::TOPIC_RESPONSE       => null,
        CommunicationConfig::TOPIC_HANDLERS       => [],
    ];
    /**#@-*/

    /**
     * Get array of generated topics name and related to this topic service class and methods
     *
     * @return array
     * @since 100.2.3
     */
    public function getServices();

    /**
     * Get topic name from webapi_async_config services config array by route url and http method
     *
     * @param string $routeUrl
     * @param string $httpMethod GET|POST|PUT|DELETE
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.3
     */
    public function getTopicName($routeUrl, $httpMethod);
}
