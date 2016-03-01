<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Plugin;

use Magento\Theme\Model\Theme\Plugin\Registration;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class RegistrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Model\Theme\Registration|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeRegistration;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Backend\App\AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    protected $abstractAction;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    /** @var \Magento\Theme\Model\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeCollection;

    /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeLoader;

    /** @var Registration */
    protected $plugin;

    protected function setUp()
    {
        $this->themeRegistration = $this->getMock('Magento\Theme\Model\Theme\Registration', [], [], '', false);
        $this->logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface', [], '', false);
        $this->abstractAction = $this->getMockForAbstractClass('Magento\Backend\App\AbstractAction', [], '', false);
        $this->request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface', [], '', false);
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->themeCollection = $this->getMock('Magento\Theme\Model\Theme\Collection', [], [], '', false);
        $this->themeLoader = $this->getMock('Magento\Theme\Model\ResourceModel\Theme\Collection', [], [], '', false);
        $this->plugin = new Registration(
            $this->themeRegistration,
            $this->themeCollection,
            $this->themeLoader,
            $this->logger,
            $this->appState
        );
    }

    public function testBeforeDispatch()
    {
        $theme = $this->getMock(
            'Magento\Theme\Model\Theme',
            [
                'setParentId',
                'getArea',
                'getThemePath',
                'getParentTheme',
                'getId',
                'getFullPath',
                'toArray',
                'addData',
                'save',
            ],
            [],
            '',
            false
        );
        $this->appState->expects($this->once())->method('getMode')->willReturn('default');
        $this->themeRegistration->expects($this->once())->method('register');
        $this->themeCollection->expects($this->once())->method('loadData')->willReturn([$theme]);
        $theme->expects($this->once())->method('getArea')->willReturn('frontend');
        $theme->expects($this->once())->method('getThemePath')->willReturn('Magento/luma');
        $theme->expects($this->exactly(2))->method('getParentTheme')->willReturnSelf();
        $theme->expects($this->once())->method('getId')->willReturn(1);
        $theme->expects($this->once())->method('getFullPath')->willReturn('frontend/Magento/blank');
        $theme->expects($this->once())->method('setParentId')->with(1);
        $this->themeLoader->expects($this->exactly(2))
            ->method('getThemeByFullPath')
            ->withConsecutive(
                ['frontend/Magento/blank'],
                ['frontend/Magento/luma']
            )
            ->will($this->onConsecutiveCalls(
                $theme,
                $theme
            ));
        $theme->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'title' => 'Magento Luma'
            ]);
        $theme->expects($this->once())
            ->method('addData')
            ->with([
                'title' => 'Magento Luma'
            ])
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('save');

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }

    public function testBeforeDispatchWithProductionMode()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn('production');
        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }

    public function testBeforeDispatchWithException()
    {
        $exception = new LocalizedException(new Phrase('Phrase'));
        $this->themeRegistration->expects($this->once())->method('register')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical');

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }
}
