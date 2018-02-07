<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Renderer\Conditions
     */
    protected $conditions;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_element;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditions = $this->objectManagerHelper->getObject('Magento\Rule\Model\Renderer\Conditions');
        $this->_element = $this->getMock(
            '\Magento\Framework\Data\Form\Element\AbstractElement',
            ['getRule'],
            [],
            '',
            false
        );
    }

    public function testRender()
    {
        $rule = $this->getMockBuilder('Magento\Rule\Model\AbstractModel')
            ->setMethods(['getConditions', '__sleep', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $conditions = $this->getMock('\Magento\Rule\Model\Condition\Combine', ['asHtmlRecursive'], [], '', false);

        $this->_element->expects($this->any())
            ->method('getRule')
            ->will($this->returnValue($rule));

        $rule->expects($this->any())
            ->method('getConditions')
            ->will($this->returnValue($conditions));

        $conditions->expects($this->once())
            ->method('asHtmlRecursive')
            ->will($this->returnValue('conditions html'));

        $this->assertEquals('conditions html', $this->conditions->render($this->_element));
    }
}
