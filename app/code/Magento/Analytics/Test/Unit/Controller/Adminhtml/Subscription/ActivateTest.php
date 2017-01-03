<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\Subscription\Activate;


use Magento\Analytics\Controller\Adminhtml\Subscription\Activate;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ActivateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Activate
     */
    private $activateController;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->activateController = $this->objectManagerHelper->getObject(
            Activate::class,
            [
                'resultFactory' => $this->resultFactoryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteSubscriptionActivatedSuccessfully()
    {
        $successResult = [
            'success' => true,
            'error_message' => '',
        ];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($successResult)
            ->willReturnSelf();
        $this->assertSame(
            $this->resultJsonMock,
            $this->activateController->execute()
        );
    }
}
