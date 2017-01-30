<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Block\Form\Container
 */
namespace Magento\Payment\Test\Unit\Block\Form;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Payment\Block\Form\Container::getChildBlock
     */
    public function testSetMethodFormTemplate()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $childBlockA = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template');
        $childBlockB = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template');

        $func = function ($blockName) use ($childBlockA, $childBlockB) {
            switch ($blockName) {
                case 'payment.method.a':
                    return $childBlockA;
                case 'payment.method.b':
                    return $childBlockB;
            }
            return null;
        };
        $block = $this->getMock('Magento\Payment\Block\Form\Container', ['getChildBlock'], [], '', false);
        $block->expects($this->atLeastOnce())->method('getChildBlock')->will($this->returnCallback($func));

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlockA->getTemplate());
        $this->assertNotEquals($template, $childBlockB->getTemplate());

        $block->setMethodFormTemplate('a', $template);
        $this->assertEquals($template, $childBlockA->getTemplate()); // Template is set to the block
        $this->assertNotEquals($template, $childBlockB->getTemplate()); // Template is not propagated to other blocks
    }
}
