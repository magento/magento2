<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\External;

use Magento\Analytics\Controller\Adminhtml\BasicTier\SignUp;
use Magento\Config\Model\Config;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SignupTest
 */
class SignUpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var SignUp
     */
    private $signUpController;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->signUpController = $this->objectManagerHelper->getObject(
            SignUp::class,
            [
                'config' => $this->configMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $basicTierUrlPath = 'analytics/url/basic_tier';
        $this->configMock->expects($this->once())
            ->method('getConfigDataValue')
            ->with($basicTierUrlPath)
            ->willReturn('value');
        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())->method('setUrl')->with('value')->willReturnSelf();
        $this->assertEquals($this->redirectMock, $this->signUpController->execute());
    }
}
