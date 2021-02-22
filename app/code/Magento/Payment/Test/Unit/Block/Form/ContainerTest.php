<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Block\Form\Container
 */
namespace Magento\Payment\Test\Unit\Block\Form;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Payment\Block\Form\Container::getChildBlock
     */
    public function testSetMethodFormTemplate()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $childBlockA = $objectManagerHelper->getObject(\Magento\Framework\View\Element\Template::class);
        $childBlockB = $objectManagerHelper->getObject(\Magento\Framework\View\Element\Template::class);

        $func = function ($blockName) use ($childBlockA, $childBlockB) {
            switch ($blockName) {
                case 'payment.method.a':
                    return $childBlockA;
                case 'payment.method.b':
                    return $childBlockB;
            }
            return null;
        };
        $block = $this->createPartialMock(\Magento\Payment\Block\Form\Container::class, ['getChildBlock']);
        $block->expects($this->atLeastOnce())->method('getChildBlock')->willReturnCallback($func);

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlockA->getTemplate());
        $this->assertNotEquals($template, $childBlockB->getTemplate());

        $block->setMethodFormTemplate('a', $template);
        $this->assertEquals($template, $childBlockA->getTemplate()); // Template is set to the block
        $this->assertNotEquals($template, $childBlockB->getTemplate()); // Template is not propagated to other blocks
    }
}
