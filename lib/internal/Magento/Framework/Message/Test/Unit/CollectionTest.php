<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\Collection;
use Magento\Framework\Message\Error;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Notice;
use Magento\Framework\Message\Success;
use Magento\Framework\Message\Warning;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Message\Collection test case
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(Collection::class);
    }

    /**
     * @covers \Magento\Framework\Message\Collection::addMessage
     * @covers \Magento\Framework\Message\Collection::getItemsByType
     */
    public function testAddMessage()
    {
        $messages = [
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Error::class),
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
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Notice::class),
            $this->objectManager->getObject(Notice::class),
            $this->objectManager->getObject(Warning::class),
            $this->objectManager->getObject(Warning::class),
            $this->objectManager->getObject(Success::class),
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
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Notice::class),
            $this->objectManager->getObject(Success::class),
            $this->objectManager->getObject(Notice::class),
            $this->objectManager->getObject(Success::class),
            $this->objectManager->getObject(Warning::class),
            $this->objectManager->getObject(Error::class),
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

        $this->assertCount($this->model->getCount(), $messages);

        foreach ($messageTypes as $type => $count) {
            $messagesByType = $this->model->getItemsByType($type);
            $this->assertEquals($count, $this->model->getCountByType($type));
            $this->assertCount($count, $messagesByType);

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
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Notice::class),
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Warning::class),
            $this->objectManager->getObject(Error::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals($this->model->getItemsByType(MessageInterface::TYPE_ERROR), $this->model->getErrors());
        $this->assertCount(4, $this->model->getErrors());
    }

    /**
     * @covers \Magento\Framework\Message\Collection::getMessageByIdentifier
     * @covers \Magento\Framework\Message\Collection::deleteMessageByIdentifier
     */
    public function testGetMessageByIdentifier()
    {
        $messages = [
            $this->objectManager->getObject(Error::class)->setIdentifier('error_id'),
            $this->objectManager->getObject(Notice::class)->setIdentifier('notice_id'),
            $this->objectManager->getObject(Warning::class)->setIdentifier('warning_id'),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $message = $this->model->getMessageByIdentifier('notice_id');
        $this->assertEquals(MessageInterface::TYPE_NOTICE, $message->getType());
        $this->assertEquals('notice_id', $message->getIdentifier());

        $this->assertCount($this->model->getCount(), $messages);
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
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Warning::class),
            $this->objectManager->getObject(Notice::class),
            $this->objectManager->getObject(Success::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertCount($this->model->getCount(), $messages);
        $this->model->clear();
        $this->assertEmpty($this->model->getCount());
    }

    /**
     * @covers \Magento\Framework\Message\Collection::clear
     */
    public function testClearWithSticky()
    {
        $messages = [
            $this->objectManager->getObject(Error::class),
            $this->objectManager->getObject(Warning::class),
            $this->objectManager->getObject(Notice::class)->setIsSticky(true),
            $this->objectManager->getObject(Success::class),
        ];

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertCount($this->model->getCount(), $messages);
        $this->model->clear();
        $this->assertEquals(1, $this->model->getCount());
    }
}
