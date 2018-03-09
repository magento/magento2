<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for MessageProcessorLoader.
 */
class MessageProcessorLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergedMessageProcessor;

    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultMessageProcessor;

    /**
     * @var \Magento\Framework\MessageQueue\MessageProcessorLoader
     */
    private $messageProcessorLoader;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mergedMessageProcessor = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->defaultMessageProcessor = $this
            ->getMockBuilder(\Magento\Framework\MessageQueue\MessageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->messageProcessorLoader = $objectManagerHelper->getObject(
            \Magento\Framework\MessageQueue\MessageProcessorLoader::class,
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
            \Magento\Framework\MessageQueue\MessageProcessorInterface::class,
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
        $mergedMessage = $this->getMockBuilder(\Magento\Framework\MessageQueue\MergedMessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            [$mergedMessage],
            [$message]
        ];
    }
}
