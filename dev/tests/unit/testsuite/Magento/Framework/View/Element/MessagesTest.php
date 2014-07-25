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

/**
 * Test for view Messages model
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\ManagerInterface;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Messages
     */
    protected $messages;

    /**
     * @var \Magento\Framework\Message\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageFactory;

    /**
     * @var \Magento\Framework\Message\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    protected function setUp()
    {
        $this->collectionFactory = $this->getMockBuilder('Magento\Framework\Message\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageFactory = $this->getMockBuilder('Magento\Framework\Message\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->messages = $objectManager->getObject('Magento\Framework\View\Element\Messages', [
            'collectionFactory' => $this->collectionFactory,
            'messageFactory' => $this->messageFactory
        ]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Message\Collection
     */
    protected function initMessageCollection()
    {
        $collection = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));
        return $collection;
    }

    public function testSetMessages()
    {
        $collection = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->never())->method('create');

        $this->messages->setMessages($collection);
        $this->assertSame($collection, $this->messages->getMessageCollection());
    }

    public function testGetMessageCollection()
    {
        $collection = $this->initMessageCollection();

        $this->assertSame($collection, $this->messages->getMessageCollection());
    }

    public function testAddMessages()
    {
        $messageOne = $this->getMock('Magento\Framework\Message\MessageInterface');
        $messageTwo = $this->getMock('Magento\Framework\Message\MessageInterface');

        $arrayMessages = [$messageOne, $messageTwo];

        $collection = $this->initMessageCollection();

        $collection->expects($this->at(0))
            ->method('addMessage')
            ->with($messageOne);
        $collection->expects($this->at(1))
            ->method('addMessage')
            ->with($messageTwo);

        $collectionForAdd = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionForAdd->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue($arrayMessages));

        $this->assertSame($this->messages, $this->messages->addMessages($collectionForAdd));
    }

    public function testAddMessage()
    {
        $message = $this->getMock('Magento\Framework\Message\MessageInterface');

        $collection = $this->initMessageCollection();

        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addMessage($message));
    }

    public function testAddError()
    {
        $messageText = 'Some message error text';

        $message = $this->getMock('Magento\Framework\Message\MessageInterface');

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_ERROR, $messageText)
            ->will($this->returnValue($message));

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addError($messageText));
    }

    public function testAddWarning()
    {
        $messageText = 'Some message warning text';

        $message = $this->getMock('Magento\Framework\Message\MessageInterface');

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_WARNING, $messageText)
            ->will($this->returnValue($message));

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addWarning($messageText));
    }

    public function testAddNotice()
    {
        $messageText = 'Some message notice text';

        $message = $this->getMock('Magento\Framework\Message\MessageInterface');

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_NOTICE, $messageText)
            ->will($this->returnValue($message));

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addNotice($messageText));
    }

    public function testAddSuccess()
    {
        $messageText = 'Some message success text';

        $message = $this->getMock('Magento\Framework\Message\MessageInterface');

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_SUCCESS, $messageText)
            ->will($this->returnValue($message));

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addSuccess($messageText));
    }

    public function testGetMessagesByType()
    {
        $messageType = MessageInterface::TYPE_SUCCESS;
        $resultMessages = [$this->getMock('Magento\Framework\Message\MessageInterface')];

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('getItemsByType')
            ->with($messageType)
            ->will($this->returnValue($resultMessages));

        $this->assertSame($resultMessages, $this->messages->getMessagesByType($messageType));
    }

    public function testGetMessageTypes()
    {
        $types = array(
            MessageInterface::TYPE_ERROR,
            MessageInterface::TYPE_WARNING,
            MessageInterface::TYPE_NOTICE,
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertEquals($types, $this->messages->getMessageTypes());
    }

    public function testGetCacheKeyInfo()
    {
        $emptyMessagesCacheKey = ['storage_types' => 'a:0:{}'];
        $this->assertEquals($emptyMessagesCacheKey, $this->messages->getCacheKeyInfo());

        $messagesCacheKey = ['storage_types' => 'a:1:{i:0;s:7:"default";}'];
        $this->messages->addStorageType(ManagerInterface::DEFAULT_GROUP);
        $this->assertEquals($messagesCacheKey, $this->messages->getCacheKeyInfo());
    }
}
