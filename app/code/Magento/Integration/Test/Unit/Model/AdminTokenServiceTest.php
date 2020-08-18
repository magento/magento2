<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\AdminTokenService;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Integration\Model\AdminTokenService class.
 */
class AdminTokenServiceTest extends TestCase
{
    /** \Magento\Integration\Model\AdminTokenService */
    protected $_tokenService;

    /** \Magento\Integration\Model\Oauth\TokenFactory|MockObject */
    protected $_tokenFactoryMock;

    /** \Magento\User\Model\User|MockObject */
    protected $_userModelMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection|MockObject */
    protected $_tokenModelCollectionMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory|MockObject */
    protected $_tokenModelCollectionFactoryMock;

    /** @var CredentialsValidator|MockObject */
    protected $validatorHelperMock;

    /** @var Token|MockObject */
    private $_tokenMock;

    protected function setUp(): void
    {
        $this->_tokenFactoryMock = $this->getMockBuilder(TokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactoryMock->expects($this->any())->method('create')->willReturn($this->_tokenMock);

        $this->_userModelMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tokenMock = $this->getMockBuilder(Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['getToken', 'loadByAdminId', 'delete', '__wakeup'])->getMock();

        $this->_tokenModelCollectionMock = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['addFilterByAdminId', 'getSize', '__wakeup', '_beforeLoad', '_afterLoad', 'getIterator', '_fetchAll']
            )->getMock();

        $this->_tokenModelCollectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )->setMethods(['create'])->disableOriginalConstructor()
            ->getMock();

        $this->_tokenModelCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_tokenModelCollectionMock);

        $this->validatorHelperMock = $this->getMockBuilder(
            CredentialsValidator::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_tokenService = new AdminTokenService(
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

    public function testRevokeAdminAccessTokenCannotRevoked()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
            ->willThrowException($exception);
        $this->_tokenService->revokeAdminAccessToken($adminId);
    }
}
