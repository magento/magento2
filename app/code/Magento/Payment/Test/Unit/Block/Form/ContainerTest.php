<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Payment\Block\Form\Container
 */
namespace Magento\Payment\Test\Unit\Block\Form;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Block\Form\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @covers \Magento\Payment\Block\Form\Container::getChildBlock
     */
    public function testSetMethodFormTemplate()
    {
        $objectManagerHelper = new ObjectManager($this);
        $childBlockA = $objectManagerHelper->getObject(Template::class);
        $childBlockB = $objectManagerHelper->getObject(Template::class);

        $func = function ($blockName) use ($childBlockA, $childBlockB) {
            switch ($blockName) {
                case 'payment.method.a':
                    return $childBlockA;
                case 'payment.method.b':
                    return $childBlockB;
            }
            return null;
        };
        $block = $this->createPartialMock(Container::class, ['getChildBlock']);
        $block->expects($this->atLeastOnce())->method('getChildBlock')->willReturnCallback($func);

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlockA->getTemplate());
        $this->assertNotEquals($template, $childBlockB->getTemplate());

        $block->setMethodFormTemplate('a', $template);
        $this->assertEquals($template, $childBlockA->getTemplate()); // Template is set to the block
        $this->assertNotEquals($template, $childBlockB->getTemplate()); // Template is not propagated to other blocks
    }
}
