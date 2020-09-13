<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\CustomerTokenService;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerTokenServiceTest extends TestCase
{
    /** \Magento\Integration\Model\CustomerTokenService */
    protected $_tokenService;

    /** \Magento\Integration\Model\Oauth\TokenFactory|MockObject */
    protected $_tokenFactoryMock;

    /** \Magento\Customer\Api\AccountManagementInterface|MockObject */
    protected $_accountManagementMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection|MockObject */
    protected $_tokenModelCollectionMock;

    /** MockObject */
    protected $_tokenModelCollectionFactoryMock;

    /** @var CredentialsValidator|MockObject */
    protected $validatorHelperMock;

    /** @var Token|MockObject */
    private $_tokenMock;

    /** @var ManagerInterface|MockObject */
    protected $manager;

    protected function setUp(): void
    {
        $this->_tokenFactoryMock = $this->getMockBuilder(TokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactoryMock->expects($this->any())->method('create')->willReturn($this->_tokenMock);

        $this->_accountManagementMock = $this
            ->getMockBuilder(AccountManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_tokenMock = $this->getMockBuilder(Token::class)
            ->disableOriginalConstructor()
            ->setMethods(['getToken', 'loadByCustomerId', 'delete', '__wakeup'])->getMock();

        $this->_tokenModelCollectionMock = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(
                [
                    'addFilterByCustomerId',
                    'getSize',
                    '__wakeup',
                    '_beforeLoad',
                    '_afterLoad',
                    'getIterator',
                    '_fetchAll'
                ]
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

        $this->manager = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->_tokenService = new CustomerTokenService(
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

    public function testRevokeCustomerAccessTokenWithoutCustomerId()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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

    public function testRevokeCustomerAccessTokenCannotRevoked()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
            ->willThrowException($exception);
        $this->_tokenService->revokeCustomerAccessToken($customerId);
    }
}
