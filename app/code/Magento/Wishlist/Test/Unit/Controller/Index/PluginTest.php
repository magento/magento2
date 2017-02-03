<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Controller\Index;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\AuthenticationStateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationState;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirector;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->authenticationState = $this->getMock('Magento\Wishlist\Model\AuthenticationState', [], [], '', false);
        $this->config = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $this->redirector = $this->getMock('\Magento\Store\App\Response\Redirect', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
    }

    protected function tearDown()
    {
        unset(
            $this->customerSession,
            $this->authenticationState,
            $this->config,
            $this->redirector,
            $this->request
        );
    }

    protected function getPlugin()
    {
        return new \Magento\Wishlist\Controller\Index\Plugin(
            $this->customerSession,
            $this->authenticationState,
            $this->config,
            $this->redirector
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testBeforeDispatch()
    {
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $indexController = $this->getMock('Magento\Wishlist\Controller\Index\Index', [], [], '', false);

        $actionFlag
            ->expects($this->once())
            ->method('set')
            ->with('', 'no-dispatch', true)
            ->willReturn(true);

        $indexController
            ->expects($this->once())
            ->method('getActionFlag')
            ->willReturn($actionFlag);

        $this->authenticationState
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->customerSession
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(false);

        $this->redirector
            ->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn('http://referer-url.com');

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(['product' => 1]);

        $this->customerSession
            ->expects($this->at(1))
            ->method('__call')
            ->with('getBeforeWishlistUrl', [])
            ->willReturn(false);
        $this->customerSession
            ->expects($this->at(2))
            ->method('__call')
            ->with('setBeforeWishlistUrl', ['http://referer-url.com'])
            ->willReturn(false);
        $this->customerSession
            ->expects($this->at(3))
            ->method('__call')
            ->with('setBeforeWishlistRequest', [['product' => 1]])
            ->willReturn(true);

        $this->config
            ->expects($this->once())
            ->method('isSetFlag')
            ->with('wishlist/general/active')
            ->willReturn(false);

        $this->getPlugin()->beforeDispatch($indexController, $this->request);
    }
}
