<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Factory test case
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Framework\Message\Factory::class);
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate($messageType)
    {
        $message = $this->model->create($messageType, 'some text');
        $this->assertInstanceOf(\Magento\Framework\Message\MessageInterface::class, $message);
    }

    public function createProvider()
    {
        return [
            [MessageInterface::TYPE_SUCCESS],
            [MessageInterface::TYPE_NOTICE],
            [MessageInterface::TYPE_WARNING],
            [MessageInterface::TYPE_ERROR]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong message type
     */
    public function testCreateWrong()
    {
        $this->model->create('Wrong', 'some text');
    }
}
