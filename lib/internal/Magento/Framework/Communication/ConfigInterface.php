<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class for accessing to communication configuration.
 */
interface ConfigInterface
{
    const TOPICS = 'topics';

    const TOPIC_NAME = 'name';
    const TOPIC_HANDLERS = 'handlers';
    const TOPIC_REQUEST = 'request';
    const TOPIC_RESPONSE = 'response';
    const TOPIC_IS_SYNCHRONOUS = 'is_synchronous';
    const TOPIC_REQUEST_TYPE = 'request_type';

    const TOPIC_REQUEST_TYPE_CLASS = 'object_interface';
    const TOPIC_REQUEST_TYPE_METHOD = 'service_method_interface';

    const SCHEMA_METHOD_PARAMS = 'method_params';
    const SCHEMA_METHOD_RETURN_TYPE = 'method_return_type';
    const SCHEMA_METHOD_HANDLER = 'method_callback';

    const SCHEMA_METHOD_PARAM_NAME = 'param_name';
    const SCHEMA_METHOD_PARAM_POSITION = 'param_position';
    const SCHEMA_METHOD_PARAM_TYPE = 'param_type';
    const SCHEMA_METHOD_PARAM_IS_REQUIRED = 'is_required';

    const HANDLER_TYPE = 'type';
    const HANDLER_METHOD = 'method';
    const HANDLER_DISABLED = 'disabled';

    /**
     * Get configuration of the specified topic.
     *
     * @param string $topicName
     * @return array
     * @throws LocalizedException
     */
    public function getTopic($topicName);

    /**
     * Get topic handlers.
     *
     * @param string $topicName
     * @return array
     */
    public function getTopicHandlers($topicName);

    /**
     * Get list of all declared topics and their configuration.
     *
     * @return array
     */
    public function getTopics();
}
