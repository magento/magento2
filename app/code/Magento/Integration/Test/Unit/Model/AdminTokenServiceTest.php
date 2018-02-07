<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Token;

/**
 * Test for \Magento\Integration\Model\AdminTokenService
 */
class AdminTokenServiceTest extends \PHPUnit_Framework_TestCase
{
    /** \Magento\Integration\Model\AdminTokenService */
    protected $_tokenService;

    /** \Magento\Integration\Model\Oauth\TokenFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenFactoryMock;

    /** \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $_userModelMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenModelCollectionMock;

    /** \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenModelCollectionFactoryMock;

    /** @var \Magento\Integration\Model\CredentialsValidator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorHelperMock;

    /** @var \Magento\Integration\Model\Oauth\Token|\PHPUnit_Framework_MockObject_MockObject */
    private $_tokenMock;

    protected function setUp()
    {
        $this->_tokenFactoryMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\TokenFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_tokenMock));

        $this->_userModelMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tokenMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token')
            ->disableOriginalConstructor()
            ->setMethods(['getToken', 'loadByAdminId', 'delete', '__wakeup'])->getMock();

        $this->_tokenModelCollectionMock = $this->getMockBuilder(
            'Magento\Integration\Model\ResourceModel\Oauth\Token\Collection'
        )->disableOriginalConstructor()->setMethods(
                ['addFilterByAdminId', 'getSize', '__wakeup', '_beforeLoad', '_afterLoad', 'getIterator']
            )->getMock();

        $this->_tokenModelCollectionFactoryMock = $this->getMockBuilder(
            'Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory'
        )->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->_tokenModelCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->_tokenModelCollectionMock));

        $this->validatorHelperMock = $this->getMockBuilder(
            'Magento\Integration\Model\CredentialsValidator'
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
            ->will($this->returnValue($this->_tokenModelCollectionMock));
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->_tokenMock])));
        $this->_tokenModelCollectionMock->expects($this->any())
            ->method('_fetchAll')
            ->with(null)
            ->will($this->returnValue(1));
        $this->_tokenMock->expects($this->once())
            ->method('delete')
            ->will($this->returnValue($this->_tokenMock));

        $this->assertTrue($this->_tokenService->revokeAdminAccessToken($adminId));
    }

    public function testRevokeAdminAccessTokenWithoutAdminId()
    {
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator()));
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByAdminId')
            ->with(null)
            ->will($this->returnValue($this->_tokenModelCollectionMock));
        $this->_tokenMock->expects($this->never())
            ->method('delete')
            ->will($this->returnValue($this->_tokenMock));
        $this->assertTrue($this->_tokenService->revokeAdminAccessToken(null));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The tokens could not be revoked.
     */
    public function testRevokeAdminAccessTokenCannotRevoked()
    {
        $exception = new \Exception();
        $adminId = 1;
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('addFilterByAdminId')
            ->with($adminId)
            ->will($this->returnValue($this->_tokenModelCollectionMock));
        $this->_tokenModelCollectionMock->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->_tokenMock])));

        $this->_tokenMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException($exception));
        $this->_tokenService->revokeAdminAccessToken($adminId);
    }
}
