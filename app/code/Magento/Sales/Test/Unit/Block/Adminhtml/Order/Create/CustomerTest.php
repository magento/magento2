<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Create\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testGetButtonsHtml()
    {
        $contextMock = $this->createPartialMock(Context::class, ['getAuthorization']);
        $authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $contextMock->expects($this->any())->method('getAuthorization')->willReturn($authorizationMock);
        $arguments = ['context' => $contextMock];

        $helper = new ObjectManager($this);
        /** @var Customer $block */
        $block = $helper->getObject(Customer::class, $arguments);

        $authorizationMock->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with('Magento_Customer::manage')
            ->willReturn(false);

        $this->assertEmpty($block->getButtonsHtml());
    }
}
