<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Controller\Adminhtml\BIEssentials;

use Magento\Analytics\Controller\Adminhtml\BIEssentials\SignUp;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SignUpTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var SignUp
     */
    private $signUpController;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->redirectMock = $this->createMock(Redirect::class);

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
