<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\Communication\Config;

use Magento\AsynchronousOperations\Model\ConfigInterface;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

class CompositeReader
{
    /**
     * Topics with type schema is always are sync
     * @see \Magento\Framework\Communication\Config\ReflectionGenerator::generateTopicConfigForServiceMethod().
     * This plugin add support for topic type schema defined as ServiceName:methodName
     * by force changing sync type to async
     * but only if topic name starts with ConfigInterface::TOPIC_PREFIX, e.g. async.*
     *
     * @param \Magento\Framework\Communication\Config\CompositeReader $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterRead(\Magento\Framework\Communication\Config\CompositeReader $subject, array $result) {
        foreach ($result[CommunicationConfig::TOPICS] as $topicName => $topicConfig) {
            if (strpos($topicName, ConfigInterface::DEFAULT_HANDLER_NAME) === 0) {
                $topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS] = false;
                $result[CommunicationConfig::TOPICS][$topicName] = $topicConfig;
            }
        }
        return $result;
    }
}
