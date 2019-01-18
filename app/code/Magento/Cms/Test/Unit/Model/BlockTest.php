<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Magento\Cms\Model\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testable Object
     *
     * @var Block
     */
    private $blockModel;

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var BlockResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder(BlockResource::class)->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManagerMock);
        $this->objectManager = new ObjectManager($this);
        $this->blockModel = $this->objectManager->getObject(
            Block::class,
            [
                'context' => $this->contextMock,
                'resource' => $this->resourceMock,
            ]
        );
    }

    /**
     * Test beforeSave method
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function testBeforeSave()
    {
        $blockId = 7;
        $this->blockModel->setData(Block::BLOCK_ID, $blockId);
        $this->blockModel->setData(Block::CONTENT, 'test');
        $this->objectManager->setBackwardCompatibleProperty($this->blockModel, '_hasDataChanges', true);
        $this->eventManagerMock->expects($this->atLeastOnce())->method('dispatch');

        $expected = $this->blockModel;
        $actual = $this->blockModel->beforeSave();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test beforeSave method
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function testBeforeSaveWithException()
    {
        $blockId = 10;
        $this->blockModel->setData(Block::BLOCK_ID, $blockId);
        $this->blockModel->setData(Block::CONTENT, 'Test block_id="' . $blockId . '".');
        $this->objectManager->setBackwardCompatibleProperty($this->blockModel, '_hasDataChanges', false);
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->setExpectedException(LocalizedException::class);
        $this->blockModel->beforeSave();
    }

    /**
     * Test getIdentities method
     *
     * @return void
     */
    public function testGetIdentities()
    {
        $result = $this->blockModel->getIdentities();
        self::assertInternalType('array', $result);
    }

    /**
     * Test getId method
     *
     * @return void
     */
    public function testGetId()
    {
        $blockId = 12;
        $this->blockModel->setData(Block::BLOCK_ID, $blockId);
        $expected = $blockId;
        $actual = $this->blockModel->getId();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test getIdentifier method
     *
     * @return void
     */
    public function testGetIdentifier()
    {
        $identifier = 'test01';
        $this->blockModel->setData(Block::IDENTIFIER, $identifier);

        $expected = $identifier;
        $actual = $this->blockModel->getIdentifier();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test getTitle method
     *
     * @return void
     */
    public function testGetTitle()
    {
        $title = 'test02';
        $this->blockModel->setData(Block::TITLE, $title);
        $expected = $title;
        $actual = $this->blockModel->getTitle();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test getContent method
     *
     * @return void
     */
    public function testGetContent()
    {
        $content = 'test03';
        $this->blockModel->setData(Block::CONTENT, $content);
        $expected = $content;
        $actual = $this->blockModel->getContent();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test getCreationTime method
     *
     * @return void
     */
    public function testGetCreationTime()
    {
        $creationTime = 'test04';
        $this->blockModel->setData(Block::CREATION_TIME, $creationTime);
        $expected = $creationTime;
        $actual = $this->blockModel->getCreationTime();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test getUpdateTime method
     *
     * @return void
     */
    public function testGetUpdateTime()
    {
        $updateTime = 'test05';
        $this->blockModel->setData(Block::UPDATE_TIME, $updateTime);
        $expected = $updateTime;
        $actual = $this->blockModel->getUpdateTime();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test isActive method
     *
     * @return void
     */
    public function testIsActive()
    {
        $isActive = true;
        $this->blockModel->setData(Block::IS_ACTIVE, $isActive);
        $result = $this->blockModel->isActive();
        self::assertTrue($result);
    }

    /**
     * Test setId method
     *
     * @return void
     */
    public function testSetId()
    {
        $blockId = 15;
        $this->blockModel->setId($blockId);
        $expected = $blockId;
        $actual = $this->blockModel->getData(Block::BLOCK_ID);
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setIdentifier method
     *
     * @return void
     */
    public function testSetIdentifier()
    {
        $identifier = 'test06';
        $this->blockModel->setIdentifier($identifier);
        $expected = $identifier;
        $actual = $this->blockModel->getData(Block::IDENTIFIER);
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setTitle method
     *
     * @return void
     */
    public function testSetTitle()
    {
        $title = 'test07';
        $this->blockModel->setTitle($title);
        $expected = $title;
        $actual = $this->blockModel->getData(Block::TITLE);
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setContent method
     *
     * @return void
     */
    public function testSetContent()
    {
        $content = 'test08';
        $this->blockModel->setContent($content);
        $expected = $content;
        $actual = $this->blockModel->getData(Block::CONTENT);
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setCreationTime method
     *
     * @return void
     */
    public function testSetCreationTime()
    {
        $creationTime = 'test09';
        $this->blockModel->setCreationTime($creationTime);
        $expected = $creationTime;
        $actual = $this->blockModel->getData(Block::CREATION_TIME);
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setUpdateTime method
     *
     * @return void
     */
    public function testSetUpdateTime()
    {
        $updateTime = 'test10';
        $this->blockModel->setUpdateTime($updateTime);
        $expected = $updateTime;
        $actual = $this->blockModel->getData(Block::UPDATE_TIME);
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setIsActive method
     *
     * @return void
     */
    public function testSetIsActive()
    {
        $this->blockModel->setIsActive(false);
        $result = $this->blockModel->getData(Block::IS_ACTIVE);
        self::assertFalse($result);
    }

    /**
     * Test getStores method
     *
     * @return void
     */
    public function testGetStores()
    {
        $stores = [1, 4, 9];
        $this->blockModel->setData('stores', $stores);
        $expected = $stores;
        $actual = $this->blockModel->getStores();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test getAvailableStatuses method
     *
     * @return void
     */
    public function testGetAvailableStatuses()
    {
        $result = $this->blockModel->getAvailableStatuses();
        self::assertInternalType('array', $result);
    }
}
