<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Token;

class CustomerTokenServiceTest extends \PHPUnit\Framework\TestCase
{
    /** \Magento\Integration\Model\CustomerTokenService */
    protected $_tokenService;

    /** \Magento\Integration\Model\Oauth\TokenFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $_tokenFactoryMock;

    /** \Magento\Customer\Api\AccountManagementInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $_accountManagementMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection|\PHPUnit\Framework\MockObject\MockObject */
    protected $_tokenModelCollectionMock;

    /** \PHPUnit\Framework\MockObject\MockObject */
    protected $_tokenModelCollectionFactoryMock;

    /** @var \Magento\Integration\Model\CredentialsValidator|\PHPUnit\Framework\MockObject\MockObject */
    protected $validatorHelperMock;

    /** @var \Magento\Integration\Model\Oauth\Token|\PHPUnit\Framework\MockObject\MockObject */
    private $_tokenMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $manager;

    protected function setUp(): void
    {
        $this->_tokenFactoryMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\TokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactoryMock->expects($this->any())->method('create')->willReturn($this->_tokenMock);

        $this->_accountManagementMock = $this
            ->getMockBuilder(\Magento\Customer\Api\AccountManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tokenMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['getToken', 'loadByCustomerId', 'delete', '__wakeup'])->getMock();

        $this->_tokenModelCollectionMock = $this->getMockBuilder(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection::class
        )->disableOriginalConstructor()->setMethods(
            ['addFilterByCustomerId', 'getSize', '__wakeup', '_beforeLoad', '_afterLoad', 'getIterator', '_fetchAll']
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

        $this->manager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->_tokenService = new \Magento\Integration\Model\CustomerTokenService(
            $this->_tokenFactoryMock,
            $this->_accountManagementMock,
            $this->_tokenModelCollectionFactoryMock,
            $this->validatorHelperMock,
            $this->manager
        );
    }

    public function testRevokeCustomerAccessToken()
    {
        $customerId = 1;

        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByCustomerId')
            ->with($customerId)
            ->willReturn($this->_tokenModelCollectionMock);
        $this->_tokenModelCollectionMock->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->_tokenMock]));
        $this->_tokenModelCollectionMock->expects($this->any())
            ->method('_fetchAll')
            ->willReturn(1);
        $this->_tokenMock->expects($this->once())
            ->method('delete')
            ->willReturn($this->_tokenMock);

        $this->assertTrue($this->_tokenService->revokeCustomerAccessToken($customerId));
    }

    /**
     */
    public function testRevokeCustomerAccessTokenWithoutCustomerId()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('This customer has no tokens.');

        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByCustomerId')
            ->with(null)
            ->willReturn($this->_tokenModelCollectionMock);
        $this->_tokenMock->expects($this->never())
            ->method('delete')
            ->willReturn($this->_tokenMock);
        $this->_tokenService->revokeCustomerAccessToken(null);
    }

    /**
     */
    public function testRevokeCustomerAccessTokenCannotRevoked()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The tokens couldn\'t be revoked.');

        $exception = new \Exception();
        $customerId = 1;
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByCustomerId')
            ->with($customerId)
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
        $this->_tokenService->revokeCustomerAccessToken($customerId);
    }
}
