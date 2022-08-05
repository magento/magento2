<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Rpc;

use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;
use PHPUnit\Framework\TestCase;

class ResponseQueueNameBuilderTest extends TestCase
{
    public function testGetQueueName()
    {
        $model = new ResponseQueueNameBuilder();
        $this->assertEquals('responseQueue.topic.01', $model->getQueueName('topic.01'));
    }
}
