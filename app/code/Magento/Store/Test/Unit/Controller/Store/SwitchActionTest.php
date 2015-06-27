<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SwitchActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Store\Controller\Store\SwitchAction */
    protected $unit;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    protected function setUp()
    {
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface', ['setRedirect', 'sendResponse']);
        $this->redirect = $this->getMock('\Magento\Framework\App\Response\RedirectInterface');
        $this->context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->context->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));
        $this->context->expects($this->any())->method('getRedirect')->will($this->returnValue($this->redirect));
        $this->unit = (new ObjectManager($this))->getObject(
            'Magento\Store\Controller\Store\SwitchAction',
            [
                'context' => $this->context,
            ]
        );
    }

    public function testExecute()
    {
        $this->redirect->expects($this->once())->method('getRedirectUrl')->willReturn('url-redirect');
        $this->response->expects($this->once())->method('setRedirect')->with('url-redirect');
        $this->unit->execute();
    }
}
