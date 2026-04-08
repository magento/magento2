<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Message;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * \Magento\Framework\Message\Factory test case
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Framework\Message\Factory::class);
    }

    #[DataProvider('createProvider')]
    public function testCreate($messageType)
    {
        $message = $this->model->create($messageType, 'some text');
        $this->assertInstanceOf(\Magento\Framework\Message\MessageInterface::class, $message);
    }

    public static function createProvider()
    {
        return [
            [MessageInterface::TYPE_SUCCESS],
            [MessageInterface::TYPE_NOTICE],
            [MessageInterface::TYPE_WARNING],
            [MessageInterface::TYPE_ERROR]
        ];
    }

    /**
     */
    public function testCreateWrong()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Wrong message type');

        $this->model->create('Wrong', 'some text');
    }
}
