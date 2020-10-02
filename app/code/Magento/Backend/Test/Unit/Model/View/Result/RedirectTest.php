<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\View\Result;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
    /** @var Redirect */
    protected $action;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Session|MockObject */
    protected $session;

    /** @var ActionFlag|MockObject */
    protected $actionFlag;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var RedirectInterface|MockObject */
    protected $redirect;

    protected $url = 'adminhtml/index';

    protected function setUp(): void
    {
        $this->session = $this->createMock(Session::class);
        $this->actionFlag = $this->createMock(ActionFlag::class);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->action = $this->objectManagerHelper->getObject(
            Redirect::class,
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
