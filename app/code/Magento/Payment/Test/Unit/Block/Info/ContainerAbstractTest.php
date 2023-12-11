<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Payment\Block\Info\AbstractContainer
 */
namespace Magento\Payment\Test\Unit\Block\Info;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Payment\Block\Info\AbstractContainer;
use Magento\Payment\Model\Info;
use PHPUnit\Framework\TestCase;

class ContainerAbstractTest extends TestCase
{
    public function testSetInfoTemplate()
    {
        $block = $this->createPartialMock(
            AbstractContainer::class,
            ['getChildBlock', 'getPaymentInfo']
        );
        $objectManagerHelper = new ObjectManager($this);
        $paymentInfo = $objectManagerHelper->getObject(Info::class);
        $methodInstance = $objectManagerHelper->getObject(Checkmo::class);
        $paymentInfo->setMethodInstance($methodInstance);
        $block->expects($this->atLeastOnce())->method('getPaymentInfo')->willReturn($paymentInfo);

        $childBlock = $objectManagerHelper->getObject(Template::class);
        $block->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'payment.info.checkmo'
        )->willReturn(
            $childBlock
        );

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlock->getTemplate());
        $block->setInfoTemplate('checkmo', $template);
        $this->assertEquals($template, $childBlock->getTemplate());
    }
}
