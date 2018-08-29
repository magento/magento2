<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Rpc;

use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;

class ResponseQueueNameBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQueueName()
    {
        $model = new ResponseQueueNameBuilder();
        $this->assertEquals('responseQueue.topic.01', $model->getQueueName('topic.01'));
    }
}
