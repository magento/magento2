<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class ShareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\Index\Share
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    protected function setUp()
    {
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->contextMock = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
        $this->resultFactoryMock = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            [],
            [],
            '',
            false
        );

        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);

        $this->model = new \Magento\Wishlist\Controller\Index\Share(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testExecute()
    {
        $resultMock = $this->getMock(\Magento\Framework\Controller\ResultInterface::class, [], [], '', false);

        $this->customerSessionMock->expects($this->once())->method('authenticate')
            ->willReturn(true);
        $this->resultFactoryMock->expects($this->once())->method('create')->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->model->execute());
    }

    public function testExecuteAuthenticationFail()
    {
        $this->customerSessionMock->expects($this->once())->method('authenticate')
            ->willReturn(false);

        $this->assertEmpty($this->model->execute());
    }
}
