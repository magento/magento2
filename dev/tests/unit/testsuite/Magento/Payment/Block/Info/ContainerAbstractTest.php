<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Block\Info\AbstractContainer
 */
namespace Magento\Payment\Block\Info;

class ContainerAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testSetInfoTemplate()
    {
        $block = $this->getMock(
            'Magento\Payment\Block\Info\AbstractContainer',
            ['getChildBlock', 'getPaymentInfo'],
            [],
            '',
            false
        );
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $paymentInfo = $objectManagerHelper->getObject('Magento\Payment\Model\Info');
        $methodInstance = $objectManagerHelper->getObject('Magento\OfflinePayments\Model\Checkmo');
        $paymentInfo->setMethodInstance($methodInstance);
        $block->expects($this->atLeastOnce())->method('getPaymentInfo')->will($this->returnValue($paymentInfo));

        $childBlock = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template');
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
