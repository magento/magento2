<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model\Oauth;

/**
 * Unit test for \Magento\Integration\Model\Oauth\Nonce
 */
class NonceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\Nonce
     */
    protected $nonceModel;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Integration\Helper\Oauth\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $oauthDataMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceCollectionMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
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
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->oauthDataMock = $this->createMock(\Magento\Integration\Helper\Oauth\Data::class);
        $this->resourceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName', 'selectByCompositeKey', 'deleteOldEntries']
        );
        $this->resourceCollectionMock = $this->createMock(\Magento\Framework\Data\Collection\AbstractDb::class);
        $this->nonceModel = new \Magento\Integration\Model\Oauth\Nonce(
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
