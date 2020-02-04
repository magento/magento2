<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;

/**
 * \Magento\Framework\Message\Collection test case
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Collection
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(\Magento\Framework\Message\Collection::class);
    }

    /**
     * @covers \Magento\Framework\Message\Collection::addMessage
     * @covers \Magento\Framework\Message\Collection::getItemsByType
     */
    public function testAddMessage()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals($messages, $this->model->getItemsByType(MessageInterface::TYPE_ERROR));
        $this->assertEmpty($this->model->getItemsByType(MessageInterface::TYPE_SUCCESS));
        $this->assertEmpty($this->model->getItemsByType(MessageInterface::TYPE_NOTICE));
        $this->assertEmpty($this->model->getItemsByType(MessageInterface::TYPE_WARNING));
    }

    /**
     * @covers \Magento\Framework\Message\Collection::addMessage
     * @covers \Magento\Framework\Message\Collection::getItems
     * @covers \Magento\Framework\Message\Collection::getLastAddedMessage
     */
    public function testGetItems()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Success::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals($messages, $this->model->getItems());
        $this->assertEquals(end($messages), $this->model->getLastAddedMessage());
    }

    /**
     * @covers \Magento\Framework\Message\Collection::addMessage
     * @covers \Magento\Framework\Message\Collection::getItemsByType
     * @covers \Magento\Framework\Message\Collection::getCount
     * @covers \Magento\Framework\Message\Collection::getCountByType
     */
    public function testGetItemsByType()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Success::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Success::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
        ];

        $messageTypes = [
            MessageInterface::TYPE_SUCCESS => 2,
            MessageInterface::TYPE_NOTICE => 2,
            MessageInterface::TYPE_WARNING => 1,
            MessageInterface::TYPE_ERROR => 2,
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals(count($messages), $this->model->getCount());

        foreach ($messageTypes as $type => $count) {
            $messagesByType = $this->model->getItemsByType($type);
            $this->assertEquals($count, $this->model->getCountByType($type));
            $this->assertEquals($count, count($messagesByType));

            /** @var MessageInterface $message */
            foreach ($messagesByType as $message) {
                $this->assertEquals($type, $message->getType());
            }
        }
    }

    /**
     * @covers \Magento\Framework\Message\Collection::addMessage
     * @covers \Magento\Framework\Message\Collection::getErrors
     */
    public function testGetErrors()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals($this->model->getItemsByType(MessageInterface::TYPE_ERROR), $this->model->getErrors());
        $this->assertEquals(4, count($this->model->getErrors()));
    }

    /**
     * @covers \Magento\Framework\Message\Collection::getMessageByIdentifier
     * @covers \Magento\Framework\Message\Collection::deleteMessageByIdentifier
     */
    public function testGetMessageByIdentifier()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class)->setIdentifier('error_id'),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class)->setIdentifier('notice_id'),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class)->setIdentifier('warning_id'),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $message = $this->model->getMessageByIdentifier('notice_id');
        $this->assertEquals(MessageInterface::TYPE_NOTICE, $message->getType());
        $this->assertEquals('notice_id', $message->getIdentifier());

        $this->assertEquals(count($messages), $this->model->getCount());
        $this->model->deleteMessageByIdentifier('notice_id');
        $this->assertEquals(count($messages) - 1, $this->model->getCount());

        $this->assertEmpty($this->model->getMessageByIdentifier('notice_id'));
    }

    /**
     * @covers \Magento\Framework\Message\Collection::clear
     */
    public function testClear()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Success::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals(count($messages), $this->model->getCount());
        $this->model->clear();
        $this->assertEmpty($this->model->getCount());
    }

    /**
     * @covers \Magento\Framework\Message\Collection::clear
     */
    public function testClearWithSticky()
    {
        $messages = [
            $this->objectManager->getObject(\Magento\Framework\Message\Error::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Warning::class),
            $this->objectManager->getObject(\Magento\Framework\Message\Notice::class)->setIsSticky(true),
            $this->objectManager->getObject(\Magento\Framework\Message\Success::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals(count($messages), $this->model->getCount());
        $this->model->clear();
        $this->assertEquals(1, $this->model->getCount());
    }
}
