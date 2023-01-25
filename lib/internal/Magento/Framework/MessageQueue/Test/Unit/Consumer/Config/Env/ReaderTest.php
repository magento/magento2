<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Env;

use Magento\Framework\MessageQueue\Config\Reader\Env;
use Magento\Framework\MessageQueue\Consumer\Config\Env\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var MockObject
     */
    private $envConfig;

    protected function setUp(): void
    {
        $this->envConfig =
            $this->getMockBuilder(Env::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->reader = new Reader($this->envConfig);
    }

    public function testRead()
    {
        $configData['consumers'] = ['consumerConfig'];
        $this->envConfig->expects($this->once())->method('read')->willReturn($configData);
        $actual = $this->reader->read();
        $this->assertEquals(['consumerConfig'], $actual);
    }

    public function testReadIfConsumerConfigNotExist()
    {
        $this->envConfig->expects($this->once())->method('read')->willReturn([]);
        $actual = $this->reader->read();
        $this->assertEquals([], $actual);
    }
}
