<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetButtonsHtml()
    {
        $contextMock = $this->createPartialMock(\Magento\Backend\Block\Template\Context::class, ['getAuthorization']);
        $authorizationMock = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
        $contextMock->expects($this->any())->method('getAuthorization')->will($this->returnValue($authorizationMock));
        $arguments = ['context' => $contextMock];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Customer $block */
        $block = $helper->getObject(\Magento\Sales\Block\Adminhtml\Order\Create\Customer::class, $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Customer::manage')
            ->will($this->returnValue(false));

        $this->assertEmpty($block->getButtonsHtml());
    }
}
