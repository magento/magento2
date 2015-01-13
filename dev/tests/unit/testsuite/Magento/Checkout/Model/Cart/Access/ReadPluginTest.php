<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\Cart\Access;

class ReadPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart\Access\ReadPlugin
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
        $this->subjectMock = $this->getMock('\Magento\Checkout\Service\V1\Cart\ReadServiceInterface');
        $this->model = new ReadPlugin($this->userContextMock);
    }

    /**
     * @param int $userType
     * @dataProvider successTypeDataProvider
     */
    public function testBeforeGetCartSuccess($userType)
    {
        $this->userContextMock->expects($this->once())->method('getUserType')->will($this->returnValue($userType));
        $this->model->beforeGetCart($this->subjectMock, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Access denied
     */
    public function testBeforeGetCartDenied()
    {
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->will($this->returnValue(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER));
        $this->model->beforeGetCart($this->subjectMock, 1);
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
    public function testBeforeGetCartListSuccess($userType)
    {
        $this->userContextMock->expects($this->once())->method('getUserType')->will($this->returnValue($userType));
        $this->model->beforeGetCartList(
            $this->subjectMock,
            $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Access denied
     */
    public function testBeforeGetCartListDenied()
    {
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->will($this->returnValue(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER));
        $this->model->beforeGetCartList(
            $this->subjectMock,
            $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false)
        );
    }
}
