<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\BIEssentials;

use Magento\Analytics\Controller\Adminhtml\BIEssentials\SignUp;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SignUpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var SignUp
     */
    private $signUpController;

    /**
     * @var RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    private $redirectMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        $urlBIEssentialsConfigPath = 'analytics/url/bi_essentials';
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with($urlBIEssentialsConfigPath)
            ->willReturn('value');
        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())->method('setUrl')->with('value')->willReturnSelf();
        $this->assertEquals($this->redirectMock, $this->signUpController->execute());
    }
}
