<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Oauth;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Integration\Helper\Oauth\Data;
use Magento\Integration\Model\Oauth\Nonce;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\Oauth\Nonce
 */
class NonceTest extends TestCase
{
    /**
     * @var Nonce
     */
    protected $nonceModel;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var Data|MockObject
     */
    protected $oauthDataMock;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resourceMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceCollectionMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $this->registryMock = $this->createMock(Registry::class);
        $this->oauthDataMock = $this->createMock(Data::class);
        $this->resourceMock = $this->getMockForAbstractClass(
            AbstractResource::class,
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName', 'selectByCompositeKey', 'deleteOldEntries']
        );
        $this->resourceCollectionMock = $this->createMock(AbstractDb::class);
        $this->nonceModel = new Nonce(
            $this->contextMock,
            $this->registryMock,
            $this->oauthDataMock,
            $this->resourceMock,
            $this->resourceCollectionMock
        );
    }

    public function testAfterSave()
    {
        $this->oauthDataMock->expects($this->once())
            ->method('isCleanupProbability')
            ->willReturn(true);

        $this->oauthDataMock->expects($this->once())
            ->method('getCleanupExpirationPeriod')
            ->willReturn(30);

        $this->resourceMock->expects($this->once())
            ->method('deleteOldEntries')
            ->with(30)
            ->willReturn(1);

        $this->assertEquals($this->nonceModel, $this->nonceModel->afterSave());
    }

    public function testAfterSaveNoCleanupProbability()
    {
        $this->oauthDataMock->expects($this->once())
            ->method('isCleanupProbability')
            ->willReturn(false);

        $this->oauthDataMock->expects($this->never())
            ->method('getCleanupExpirationPeriod');

        $this->resourceMock->expects($this->never())
            ->method('deleteOldEntries');

        $this->assertEquals($this->nonceModel, $this->nonceModel->afterSave());
    }

    public function testLoadByCompositeKey()
    {
        $expectedData = ['testData'];
        $nonce = 'testNonce';
        $consumerId = 1;

        $this->resourceMock->expects($this->once())
            ->method('selectByCompositeKey')
            ->with($nonce, $consumerId)
            ->willReturn($expectedData);
        $this->nonceModel->loadByCompositeKey($nonce, $consumerId);

        $this->assertEquals($expectedData, $this->nonceModel->getData());
    }
}
