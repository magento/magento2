<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Test\Unit;

use Magento\Framework\Logger\Configuration\ChannelConfigurationParserInterface;
use Magento\Framework\Logger\Configuration\ParsedChannelConfiguration;
use Magento\Framework\Logger\Logchannel;
use Monolog\Handler\TestHandler;
use Monolog\Processor\UidProcessor;

class LogchannelTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectHandlerAssigned()
    {
        $handler = new TestHandler();

        $channelConfigParser = $this->createMock(ChannelConfigurationParserInterface::class);
        $channelConfigParser
            ->method('parseConfiguration')
            ->willReturn(new ParsedChannelConfiguration([$handler], []));

        $logger = new Logchannel($channelConfigParser);

        $handlers = $logger->getHandlers();

        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(TestHandler::class, $handlers[0]);
    }

    public function testCorrectProcessorAssigned()
    {
        $processor = new UidProcessor();

        $channelConfigParser = $this->createMock(ChannelConfigurationParserInterface::class);
        $channelConfigParser
            ->method('parseConfiguration')
            ->willReturn(new ParsedChannelConfiguration([], [$processor]));

        $logger = new Logchannel($channelConfigParser);

        $processors = $logger->getProcessors();

        $this->assertCount(1, $processors);
        $this->assertInstanceOf(UidProcessor::class, $processors[0]);
    }

    /**
     * @depends testCorrectHandlerAssigned
     * @depends testCorrectProcessorAssigned
     */
    public function testAddRecord()
    {
        $handler = new TestHandler();

        $channelConfigParser = $this->createMock(ChannelConfigurationParserInterface::class);
        $channelConfigParser
            ->method('parseConfiguration')
            ->willReturn(new ParsedChannelConfiguration([$handler], []));

        $logger = new Logchannel($channelConfigParser);

        $logger->addError('test');
        list($record) = $handler->getRecords();

        $this->assertSame('test', $record['message']);
    }

    /**
     * @depends testAddRecord
     */
    public function testAddRecordAsException()
    {
        $handler = new TestHandler();

        $channelConfigParser = $this->createMock(ChannelConfigurationParserInterface::class);
        $channelConfigParser
            ->method('parseConfiguration')
            ->willReturn(new ParsedChannelConfiguration([$handler], []));

        $logger = new Logchannel($channelConfigParser);

        $logger->addError(new \Exception('Some exception'));
        list($record) = $handler->getRecords();

        $this->assertInstanceOf(\Exception::class, $record['context']['exception']);
        $this->assertSame('Some exception', $record['message']);
    }
}
