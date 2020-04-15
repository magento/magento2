<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConditionsTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\Data\Form\Element\AbstractElement|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_element;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditions = $this->objectManagerHelper->getObject(\Magento\Rule\Model\Renderer\Conditions::class);
        $this->_element = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['getRule']
        );
    }

    public function testRender()
    {
        $rule = $this->getMockBuilder(\Magento\Rule\Model\AbstractModel::class)
            ->setMethods(['getConditions', '__sleep', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $conditions = $this->createPartialMock(\Magento\Rule\Model\Condition\Combine::class, ['asHtmlRecursive']);

        $this->_element->expects($this->any())
            ->method('getRule')
            ->willReturn($rule);

        $rule->expects($this->any())
            ->method('getConditions')
            ->willReturn($conditions);

        $conditions->expects($this->once())
            ->method('asHtmlRecursive')
            ->willReturn('conditions html');

        $this->assertEquals('conditions html', $this->conditions->render($this->_element));
    }
}
