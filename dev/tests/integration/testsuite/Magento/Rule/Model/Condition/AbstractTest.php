<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Rule\Model\Condition\AbstractCondition
 */
namespace Magento\Rule\Model\Condition;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValueElement()
    {
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $context = $objectManager->create('Magento\Rule\Model\Condition\Context', ['layout' => $layoutMock]);

        /** @var \Magento\Rule\Model\Condition\AbstractCondition $model */
        $model = $this->getMockForAbstractClass(
            'Magento\Rule\Model\Condition\AbstractCondition',
            [$context],
            '',
            true,
            true,
            true,
            ['getValueElementRenderer']
        );
        $editableBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Rule\Block\Editable'
        );
        $model->expects($this->any())->method('getValueElementRenderer')->will($this->returnValue($editableBlock));

        $rule = $this->getMockBuilder('Magento\Rule\Model\AbstractModel')
            ->setMethods(['getForm'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $rule->expects($this->any())
            ->method('getForm')
            ->willReturn(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Framework\Data\Form')
            );
        $model->setRule($rule);

        $property = new \ReflectionProperty('Magento\Rule\Model\Condition\AbstractCondition', '_inputType');
        $property->setAccessible(true);
        $property->setValue($model, 'date');

        $element = $model->getValueElement();
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
