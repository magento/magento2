<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp;

/**
 * An AMQP Producer to handle publishing a message.
 */
class AmqpProducer implements ProducerInterface
{
    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        /* do nothing for now */
    }
}
