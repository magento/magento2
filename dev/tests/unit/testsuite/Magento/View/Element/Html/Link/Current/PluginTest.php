<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\View\Element\Html\Link\Current;

use Magento\Framework\View\Plugin\Element\Html\Link as Plugin;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $link;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $context = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            null,
            [],
            '',
            false
        );
        $defaultPath = $this->getMock(
            'Magento\Framework\App\DefaultPath\DefaultPath',
            null,
            [],
            '',
            false
        );
        $this->link = $this->getMock(
            'Magento\Framework\View\Element\Html\Link\Current',
            [
                'getRequest',
                'getNameInLayout'
            ],
            [$context, $defaultPath]
        );
        $this->request = $this->getMock(
            'Magento\Framework\App\Request\Http',
            [
                'getModuleName',
                'getControllerName',
                'getActionName'
            ],
            [],
            '',
            false
        );
        $this->plugin = new Plugin\Current();

        $this->configuringLinkObject();
    }

    protected function configuringLinkObject()
    {
        $this->configuringRequestObject();

        $this->link
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->link
            ->expects($this->once())
            ->method('getNameInLayout')
            ->will($this->returnValue('customer-account-navigation-orders-link'));
    }


    protected function configuringRequestObject()
    {
        $this->request
            ->expects($this->once())
            ->method('getModuleName')
            ->will($this->returnValue('sales'));
        $this->request
            ->expects($this->once())
            ->method('getControllerName')
            ->will($this->returnValue('order'));
        $this->request
            ->expects($this->once())
            ->method('getActionName')
            ->will($this->returnValue('view'));
    }

    public function testIsCurrentOnSalesOrderViewPage()
    {
        $this->plugin->beforeIsCurrent($this->link);

        $this->assertTrue($this->link->getData('current'));
    }
}