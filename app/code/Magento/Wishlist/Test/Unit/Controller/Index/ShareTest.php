<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Wishlist\Controller\Index\Share;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShareTest extends TestCase
{
    /**
     * @var Share
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $resultFactoryMock;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);

        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);

        $this->model = new Share(
            $this->contextMock,
            $this->customerSessionMock
        );
    }

    public function testExecute()
    {
        $resultMock = $this->getMockForAbstractClass(ResultInterface::class);

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
