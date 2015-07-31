<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Producer to publish messages via a specific transport to a specific queue or exchange.
 */
interface PublisherInterface
{
    /**
     * Publishes a message to a specific queue or exchange.
     *
     * @param string $topicName
     * @param array|object $data
     */
    public function publish($topicName, $data);
}
