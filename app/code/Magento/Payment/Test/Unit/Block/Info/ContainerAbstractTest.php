<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Block\Info\AbstractContainer
 */
namespace Magento\Payment\Test\Unit\Block\Info;

class ContainerAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testSetInfoTemplate()
    {
        $block = $this->getMock(
            \Magento\Payment\Block\Info\AbstractContainer::class,
            ['getChildBlock', 'getPaymentInfo'],
            [],
            '',
            false
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $paymentInfo = $objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $methodInstance = $objectManagerHelper->getObject(\Magento\OfflinePayments\Model\Checkmo::class);
        $paymentInfo->setMethodInstance($methodInstance);
        $block->expects($this->atLeastOnce())->method('getPaymentInfo')->will($this->returnValue($paymentInfo));

        $childBlock = $objectManagerHelper->getObject(\Magento\Framework\View\Element\Template::class);
        $block->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'payment.info.checkmo'
        )->will(
            $this->returnValue($childBlock)
        );

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlock->getTemplate());
        $block->setInfoTemplate('checkmo', $template);
        $this->assertEquals($template, $childBlock->getTemplate());
    }
}
