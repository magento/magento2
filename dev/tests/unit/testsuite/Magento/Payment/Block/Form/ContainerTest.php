<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Payment\Block\Form\Container
 */
namespace Magento\Payment\Block\Form;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Payment\Block\Form\Container::getChildBlock
     */
    public function testSetMethodFormTemplate()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
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
        $block = $this->getMock('Magento\Payment\Block\Form\Container', array('getChildBlock'), array(), '', false);
        $block->expects($this->atLeastOnce())->method('getChildBlock')->will($this->returnCallback($func));

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlockA->getTemplate());
        $this->assertNotEquals($template, $childBlockB->getTemplate());

        $block->setMethodFormTemplate('a', $template);
        $this->assertEquals($template, $childBlockA->getTemplate()); // Template is set to the block
        $this->assertNotEquals($template, $childBlockB->getTemplate()); // Template is not propagated to other blocks
    }
}
