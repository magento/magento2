<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Cart\Access;

use \Magento\Quote\Model\Cart\Access\CartRepositoryPlugin;

class CartRepositoryPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Cart\Access\CartRepositoryPlugin
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->userContextMock = $this->getMock('Magento\Authorization\Model\UserContextInterface');
        $this->subjectMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->model = new CartRepositoryPlugin($this->userContextMock);
    }

    /**
     * @param int $userType
     * @dataProvider successTypeDataProvider
     */
    public function testBeforeGetSuccess($userType)
    {
        $this->userContextMock->expects($this->once())->method('getUserType')->will($this->returnValue($userType));
        $this->model->beforeGet($this->subjectMock, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Access denied
     */
    public function testBeforeGetCartDenied()
    {
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->will($this->returnValue(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER));
        $this->model->beforeGet($this->subjectMock, 1);
    }

    public function successTypeDataProvider()
    {
        return [
            'admin' => [\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN],
            'integration' => [\Magento\Authorization\Model\UserContextInterface::USER_TYPE_INTEGRATION],
        ];
    }

    /**
     * @param int $userType
     * @dataProvider successTypeDataProvider
     */
    public function testBeforeGetCartSuccess($userType)
    {
        $this->userContextMock->expects($this->once())->method('getUserType')->will($this->returnValue($userType));
        $this->model->beforeGetList(
            $this->subjectMock,
            $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Access denied
     */
    public function testBeforeGetListDenied()
    {
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->will($this->returnValue(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER));
        $this->model->beforeGetList(
            $this->subjectMock,
            $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false)
        );
    }
}
