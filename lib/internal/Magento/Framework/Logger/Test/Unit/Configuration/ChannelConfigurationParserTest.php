<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Logger\Test\Unit\Configuration;

use Magento\Framework\Logger\Configuration\ChannelConfigurationParser;
use Magento\Framework\Logger\Configuration\LogConfigurationProviderInterface;
use Monolog\Handler\TestHandler;
use Monolog\Processor\UidProcessor;

class ChannelConfigurationParserTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectFetchingOfHandlersByKey()
    {
        $handler = new TestHandler();

        $logConfigProvider = $this->createMock(LogConfigurationProviderInterface::class);
        $logConfigProvider
            ->method('getHandlerByKey')
            ->with($this->equalTo('test'))
            ->willReturn($handler);

        $channelConfigParser = new ChannelConfigurationParser($logConfigProvider);

        $parsedConfiguration = $channelConfigParser->parseConfiguration(
            [
                'handlers' => [ 'test' ]
            ]
        );

        $this->assertCount(1, $parsedConfiguration->getHandlers());
        $this->assertInstanceOf(TestHandler::class, $parsedConfiguration->getHandlers()[0]);
    }

    public function testCorrectFetchingOfProcessorsByKey()
    {
        $processor = new UidProcessor();

        $logConfigProvider = $this->createMock(LogConfigurationProviderInterface::class);
        $logConfigProvider
            ->method('getProcessorByKey')
            ->with($this->equalTo('uid'))
            ->willReturn($processor);

        $channelConfigParser = new ChannelConfigurationParser($logConfigProvider);

        $parsedConfiguration = $channelConfigParser->parseConfiguration(
            [
                'processors' => [ 'uid' ]
            ]
        );

        $this->assertCount(1, $parsedConfiguration->getProcessors());
        $this->assertInstanceOf(UidProcessor::class, $parsedConfiguration->getProcessors()[0]);
    }
}
