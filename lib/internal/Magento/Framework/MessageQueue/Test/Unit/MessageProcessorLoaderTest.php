<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MergedMessageInterface;
use Magento\Framework\MessageQueue\MessageProcessorInterface;
use Magento\Framework\MessageQueue\MessageProcessorLoader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for MessageProcessorLoader.
 */
class MessageProcessorLoaderTest extends TestCase
{
    /**
     * @var MessageProcessorInterface|MockObject
     */
    private $mergedMessageProcessor;

    /**
     * @var MessageProcessorInterface|MockObject
     */
    private $defaultMessageProcessor;

    /**
     * @var MessageProcessorLoader
     */
    private $messageProcessorLoader;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mergedMessageProcessor = $this
            ->getMockBuilder(MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->defaultMessageProcessor = $this
            ->getMockBuilder(MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->messageProcessorLoader = $objectManagerHelper->getObject(
            MessageProcessorLoader::class,
            [
                'mergedMessageProcessor' => $this->mergedMessageProcessor,
                'defaultMessageProcessor' => $this->defaultMessageProcessor
            ]
        );
    }

    /**
     * Test for load().
     *
     * @param $message
     * @dataProvider loadDataProvider
     */
    public function testLoad($message)
    {
        $messageTopic = 'topic';
        $messages = [
            $messageTopic => [$message]
        ];

        $this->assertInstanceOf(
            MessageProcessorInterface::class,
            $this->messageProcessorLoader->load($messages)
        );
    }

    /**
     * DataProvider for load().
     *
     * @return array
     */
    public function loadDataProvider()
    {
        $mergedMessage = $this->getMockBuilder(MergedMessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            [$mergedMessage],
            [$message]
        ];
    }
}
