<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockExtensible;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class BlockExtensibleTest
 * @package Magento\Cms\Test\Unit\Model
 */
class BlockExtensibleTest extends TestCase
{
    /**
     * @var BlockExtensible
     */
    private $blockExtensible;

    /**
     * @var BlockResource|MockObject
     */
    private $resourceMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(BlockResource::class);
        $this->objectManager = new ObjectManager($this);
        $this->blockExtensible = $this->objectManager->getObject(
            BlockExtensible::class,
            [
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
    public function testBeforeSave(): void
    {
        $blockId = 7;
        $this->blockExtensible->setData(Block::BLOCK_ID, $blockId);
        $this->blockExtensible->setData(Block::CONTENT, 'test');

        $this->objectManager->setBackwardCompatibleProperty(
            $this->blockExtensible,
            '_hasDataChanges',
            true
        );

        $this->assertSame($this->blockExtensible, $this->blockExtensible->beforeSave());
    }

    /**
     * Test beforeSave method
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function testBeforeSaveWhenBlockContentHasReferenceToItself(): void
    {
        $blockId = 10;
        $this->blockExtensible->setData(Block::BLOCK_ID, $blockId);
        $this->blockExtensible->setData(Block::CONTENT, 'Test block_id="' . $blockId . '".');

        $this->objectManager->setBackwardCompatibleProperty(
            $this->blockExtensible,
            '_hasDataChanges',
            false
        );

        $this->expectException(LocalizedException::class);
        $this->blockExtensible->beforeSave();
    }

    /**
     * Test getIdentities method
     *
     * @test
     *
     * @return void
     */
    public function testGetIdentities()
    {
        $result = $this->blockExtensible->getIdentities();
        $this->assertInternalType('array', $result);
    }

    /**
     * Test getId method
     *
     * @test
     *
     * @return void
     */
    public function testGetId()
    {
        $blockId = 12;
        $this->blockExtensible->setData(Block::BLOCK_ID, $blockId);
        $expected = $blockId;
        $actual = $this->blockExtensible->getId();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getIdentifier method
     *
     * @test
     *
     * @return void
     */
    public function testGetIdentifier()
    {
        $identifier = 'test01';
        $this->blockExtensible->setData(Block::IDENTIFIER, $identifier);
        $expected = $identifier;
        $actual = $this->blockExtensible->getIdentifier();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getTitle method
     *
     * @test
     *
     * @return void
     */
    public function testGetTitle()
    {
        $title = 'test02';
        $this->blockExtensible->setData(Block::TITLE, $title);
        $expected = $title;
        $actual = $this->blockExtensible->getTitle();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getContent method
     *
     * @test
     *
     * @return void
     */
    public function testGetContent()
    {
        $content = 'test03';
        $this->blockExtensible->setData(Block::CONTENT, $content);
        $expected = $content;
        $actual = $this->blockExtensible->getContent();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getCreationTime method
     *
     * @test
     *
     * @return void
     */
    public function testGetCreationTime()
    {
        $creationTime = 'test04';
        $this->blockExtensible->setData(Block::CREATION_TIME, $creationTime);
        $expected = $creationTime;
        $actual = $this->blockExtensible->getCreationTime();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getUpdateTime method
     *
     * @test
     *
     * @return void
     */
    public function testGetUpdateTime()
    {
        $updateTime = 'test05';
        $this->blockExtensible->setData(Block::UPDATE_TIME, $updateTime);
        $expected = $updateTime;
        $actual = $this->blockExtensible->getUpdateTime();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test isActive method
     *
     * @test
     *
     * @return void
     */
    public function testIsActive()
    {
        $isActive = true;
        $this->blockExtensible->setData(Block::IS_ACTIVE, $isActive);
        $result = $this->blockExtensible->isActive();
        $this->assertTrue($result);
    }

    /**
     * Test setId method
     *
     * @test
     *
     * @return void
     */
    public function testSetId()
    {
        $blockId = 15;
        $this->blockExtensible->setId($blockId);
        $expected = $blockId;
        $actual = $this->blockExtensible->getData(Block::BLOCK_ID);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test setIdentifier method
     *
     * @test
     *
     * @return void
     */
    public function testSetIdentifier()
    {
        $identifier = 'test06';
        $this->blockExtensible->setIdentifier($identifier);
        $expected = $identifier;
        $actual = $this->blockExtensible->getData(Block::IDENTIFIER);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test setTitle method
     *
     * @test
     *
     * @return void
     */
    public function testSetTitle()
    {
        $title = 'test07';
        $this->blockExtensible->setTitle($title);
        $expected = $title;
        $actual = $this->blockExtensible->getData(Block::TITLE);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test setContent method
     *
     * @test
     *
     * @return void
     */
    public function testSetContent()
    {
        $content = 'test08';
        $this->blockExtensible->setContent($content);
        $expected = $content;
        $actual = $this->blockExtensible->getData(Block::CONTENT);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test setCreationTime method
     *
     * @test
     *
     * @return void
     */
    public function testSetCreationTime()
    {
        $creationTime = 'test09';
        $this->blockExtensible->setCreationTime($creationTime);
        $expected = $creationTime;
        $actual = $this->blockExtensible->getData(Block::CREATION_TIME);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test setUpdateTime method
     *
     * @test
     *
     * @return void
     */
    public function testSetUpdateTime()
    {
        $updateTime = 'test10';
        $this->blockExtensible->setUpdateTime($updateTime);
        $expected = $updateTime;
        $actual = $this->blockExtensible->getData(Block::UPDATE_TIME);
        $this->assertSame($expected, $actual);
    }

    /**
     * Test setIsActive method
     *
     * @test
     *
     * @return void
     */
    public function testSetIsActive()
    {
        $this->blockExtensible->setIsActive(false);
        $result = $this->blockExtensible->getData(Block::IS_ACTIVE);
        $this->assertFalse($result);
    }

    /**
     * Test getStores method
     *
     * @test
     *
     * @return void
     */
    public function testGetStoresWhenDataIsStoredUnderStoresKey()
    {
        $stores = [1, 4, 9];
        $this->blockExtensible->setData('stores', $stores);
        $expected = $stores;
        $actual = $this->blockExtensible->getStores();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getStores method
     *
     * @test
     *
     * @return void
     */
    public function testGetStoresWhenDataIsStoreIdKey()
    {
        $stores = [1, 4, 9];
        $this->blockExtensible->setData('store_id', $stores);
        $expected = $stores;
        $actual = $this->blockExtensible->getStores();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getStores method
     *
     * @test
     *
     * @return void
     */
    public function testGetStoresWhenThereIsNoStoreData()
    {
        $actual = $this->blockExtensible->getStores();
        $this->assertSame([], $actual);
    }

    /**
     * Test getAvailableStatuses method
     *
     * @test
     *
     * @return void
     */
    public function testGetAvailableStatuses()
    {
        $result = $this->blockExtensible->getAvailableStatuses();
        $this->assertInternalType('array', $result);
    }
}
