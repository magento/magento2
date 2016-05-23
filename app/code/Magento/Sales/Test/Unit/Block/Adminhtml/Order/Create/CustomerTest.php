<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetButtonsHtml()
    {
        $contextMock = $this->getMock('Magento\Backend\Block\Template\Context', ['getAuthorization'], [], '', false);
        $authorizationMock = $this->getMock('Magento\Framework\AuthorizationInterface', [], [], '', false);
        $contextMock->expects($this->any())->method('getAuthorization')->will($this->returnValue($authorizationMock));
        $arguments = ['context' => $contextMock];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Customer $block */
        $block = $helper->getObject('Magento\Sales\Block\Adminhtml\Order\Create\Customer', $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Customer::manage')
            ->will($this->returnValue(false));

        $this->assertEmpty($block->getButtonsHtml());
    }
}
