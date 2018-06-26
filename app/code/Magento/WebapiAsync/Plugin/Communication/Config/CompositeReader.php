<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\Communication\Config;

use Magento\AsynchronousOperations\Model\ConfigInterface;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Force change topic's defined as ServiceName:methodName from sync to async.
 * Topic type will be changed if topic name starts with
 * \Magento\AsynchronousOperations\Model\ConfigInterface::TOPIC_PREFIX
 *
 * @SuppressWarnings("unused")
 */
class CompositeReader
{
    /**
     * Checking communication.xml merged configuration
     * to find topics with names started from "async"
     * \Magento\AsynchronousOperations\Model\ConfigInterface::TOPIC_PREFIX
     * and change config attribute value "is_synchronous" to false (make async)
     * for this topics.
     *
     * @param \Magento\Framework\Communication\Config\CompositeReader $subject
     * @param array $result
     *
     * @return array
     */
    public function afterRead(\Magento\Framework\Communication\Config\CompositeReader $subject, array $result)
    {
        foreach ($result[CommunicationConfig::TOPICS] as $topicName => $topicConfig) {
            if (strpos($topicName, ConfigInterface::DEFAULT_HANDLER_NAME) === 0) {
                $topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS] = false;
                $result[CommunicationConfig::TOPICS][$topicName] = $topicConfig;
            }
        }

        return $result;
    }
}
