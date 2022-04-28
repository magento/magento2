<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Controller\Adminhtml\User;

use Magento\AdobeIms\Controller\Adminhtml\User\Logout;
use Magento\AdobeImsApi\Api\LogOutInterface;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Get logout test.
 */
class LogoutTest extends TestCase
{
    /**
     * @var MockObject|LogOutInterface
     */
    private $logoutInterfaceMock;

    /**
     * @var MockObject|ActionContext
     */
    private $context;

    /**
     * @var Logout
     */
    private $getLogout;

    /**
     * @var MockObject
     */
    private $resultFactory;

    /**
     * @var MockObject
     */
    private $jsonObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->logoutInterfaceMock = $this->createMock(LogOutInterface::class);
        $this->context = $this->createMock(ActionContext::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->jsonObject = $this->createMock(Json::class);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonObject);

        $this->getLogout = new Logout(
            $this->context,
            $this->logoutInterfaceMock
        );
    }

    /**
     * Verify that user can be logout
     */
    public function testExecute(): void
    {
        $this->logoutInterfaceMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $data = ['success' => true];
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(200);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($data));
        $this->getLogout->execute();
    }

    /**
     * Verify that return will be false if there is an error in logout.
     * @throws NotFoundException
     */
    public function testExecuteWithError(): void
    {
        $result = [
            'success' => false,
        ];
        $this->logoutInterfaceMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->getLogout->execute();
    }
}
