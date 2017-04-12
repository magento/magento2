<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\View\Result;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\Model\View\Result\Redirect */
    protected $action;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlag;

    /** @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    protected $url = 'adminhtml/index';

    protected function setUp()
    {
        $this->session = $this->getMock(\Magento\Backend\Model\Session::class, [], [], '', false);
        $this->actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);
        $this->urlBuilder = $this->getMock(\Magento\Backend\Model\UrlInterface::class, [], [], '', false);
        $this->redirect = $this->getMock(
            \Magento\Framework\App\Response\RedirectInterface::class,
            [],
            [],
            '',
            false
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->action = $this->objectManagerHelper->getObject(
            \Magento\Backend\Model\View\Result\Redirect::class,
            [
                'session' => $this->session,
                'actionFlag' => $this->actionFlag,
                'redirect' => $this->redirect,
                'urlBuilder' =>$this->urlBuilder,
            ]
        );
    }

    public function testSetRefererOrBaseUrl()
    {
        $this->urlBuilder->expects($this->once())->method('getUrl')->willReturn($this->url);
        $this->redirect->expects($this->once())->method('getRedirectUrl')->with($this->url)->willReturn('test string');
        $this->action->setRefererOrBaseUrl();
    }
}
