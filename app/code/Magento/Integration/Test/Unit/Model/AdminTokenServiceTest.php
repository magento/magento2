<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\Oauth\Token;

/**
 * Test for Magento\Integration\Model\AdminTokenService class.
 */
class AdminTokenServiceTest extends \PHPUnit\Framework\TestCase
{
    /** \Magento\Integration\Model\AdminTokenService */
    protected $_tokenService;

    /** \Magento\Integration\Model\Oauth\TokenFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $_tokenFactoryMock;

    /** \Magento\User\Model\User|\PHPUnit\Framework\MockObject\MockObject */
    protected $_userModelMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection|\PHPUnit\Framework\MockObject\MockObject */
    protected $_tokenModelCollectionMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory
     * |\PHPUnit\Framework\MockObject\MockObject */
    protected $_tokenModelCollectionFactoryMock;

    /** @var \Magento\Integration\Model\CredentialsValidator|\PHPUnit\Framework\MockObject\MockObject */
    protected $validatorHelperMock;

    /** @var \Magento\Integration\Model\Oauth\Token|\PHPUnit\Framework\MockObject\MockObject */
    private $_tokenMock;

    protected function setUp(): void
    {
        $this->_tokenFactoryMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\TokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactoryMock->expects($this->any())->method('create')->willReturn($this->_tokenMock);

        $this->_userModelMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tokenMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['getToken', 'loadByAdminId', 'delete', '__wakeup'])->getMock();

        $this->_tokenModelCollectionMock = $this->getMockBuilder(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection::class
        )->disableOriginalConstructor()->setMethods(
            ['addFilterByAdminId', 'getSize', '__wakeup', '_beforeLoad', '_afterLoad', 'getIterator', '_fetchAll']
        )->getMock();

        $this->_tokenModelCollectionFactoryMock = $this->getMockBuilder(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory::class
        )->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->_tokenModelCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_tokenModelCollectionMock);

        $this->validatorHelperMock = $this->getMockBuilder(
            \Magento\Integration\Model\CredentialsValidator::class
        )->disableOriginalConstructor()->getMock();

        $this->_tokenService = new \Magento\Integration\Model\AdminTokenService(
            $this->_tokenFactoryMock,
            $this->_userModelMock,
            $this->_tokenModelCollectionFactoryMock,
            $this->validatorHelperMock
        );
    }

    public function testRevokeAdminAccessToken()
    {
        $adminId = 1;

        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByAdminId')
            ->with($adminId)
            ->willReturn($this->_tokenModelCollectionMock);
        $this->_tokenModelCollectionMock->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->_tokenMock]));
        $this->_tokenModelCollectionMock->expects($this->any())
            ->method('_fetchAll')
            ->with(null)
            ->willReturn(1);
        $this->_tokenMock->expects($this->once())
            ->method('delete')
            ->willReturn($this->_tokenMock);

        $this->assertTrue($this->_tokenService->revokeAdminAccessToken($adminId));
    }

    public function testRevokeAdminAccessTokenWithoutAdminId()
    {
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByAdminId')
            ->with(null)
            ->willReturn($this->_tokenModelCollectionMock);
        $this->_tokenMock->expects($this->never())
            ->method('delete')
            ->willReturn($this->_tokenMock);
        $this->_tokenService->revokeAdminAccessToken(null);
    }

    /**
     */
    public function testRevokeAdminAccessTokenCannotRevoked()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The tokens couldn\'t be revoked.');

        $exception = new \Exception();
        $adminId = 1;
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByAdminId')
            ->with($adminId)
            ->willReturn($this->_tokenModelCollectionMock);
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->_tokenMock]));

        $this->_tokenMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException($exception));
        $this->_tokenService->revokeAdminAccessToken($adminId);
    }
}
