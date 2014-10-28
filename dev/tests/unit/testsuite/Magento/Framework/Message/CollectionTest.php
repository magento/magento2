<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Collection test case
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Collection
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject('Magento\Framework\Message\Collection');
    }

    /**
     * @covers \Magento\Framework\Message\Collection::addMessage
     * @covers \Magento\Framework\Message\Collection::getItemsByType
     */
    public function testAddMessage()
    {
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Error')
        );

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
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning'),
            $this->objectManager->getObject('Magento\Framework\Message\Success')
        );

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
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice'),
            $this->objectManager->getObject('Magento\Framework\Message\Success'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice'),
            $this->objectManager->getObject('Magento\Framework\Message\Success'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning'),
            $this->objectManager->getObject('Magento\Framework\Message\Error')
        );

        $messageTypes = array(
            MessageInterface::TYPE_SUCCESS => 2,
            MessageInterface::TYPE_NOTICE => 2,
            MessageInterface::TYPE_WARNING => 1,
            MessageInterface::TYPE_ERROR => 2
        );

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
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice'),
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning'),
            $this->objectManager->getObject('Magento\Framework\Message\Error')
        );

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
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error')->setIdentifier('error_id'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice')->setIdentifier('notice_id'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning')->setIdentifier('warning_id')
        );

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
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice'),
            $this->objectManager->getObject('Magento\Framework\Message\Success')
        );

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
        $messages = array(
            $this->objectManager->getObject('Magento\Framework\Message\Error'),
            $this->objectManager->getObject('Magento\Framework\Message\Warning'),
            $this->objectManager->getObject('Magento\Framework\Message\Notice')->setIsSticky(true),
            $this->objectManager->getObject('Magento\Framework\Message\Success')
        );

        foreach ($messages as $message) {
            $this->model->addMessage($message);
        }

        $this->assertEquals(count($messages), $this->model->getCount());
        $this->model->clear();
        $this->assertEquals(1, $this->model->getCount());
    }
}
