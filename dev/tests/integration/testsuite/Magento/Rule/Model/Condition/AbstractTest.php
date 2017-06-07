<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $layoutMock = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $context = $objectManager->create(\Magento\Rule\Model\Condition\Context::class, ['layout' => $layoutMock]);

        /** @var \Magento\Rule\Model\Condition\AbstractCondition $model */
        $model = $this->getMockForAbstractClass(
            \Magento\Rule\Model\Condition\AbstractCondition::class,
            [$context],
            '',
            true,
            true,
            true,
            ['getValueElementRenderer']
        );
        $editableBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Rule\Block\Editable::class
        );
        $model->expects($this->any())->method('getValueElementRenderer')->will($this->returnValue($editableBlock));

        $rule = $this->getMockBuilder(\Magento\Rule\Model\AbstractModel::class)
            ->setMethods(['getForm'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $rule->expects($this->any())
            ->method('getForm')
            ->willReturn(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Framework\Data\Form::class)
            );
        $model->setRule($rule);

        $property = new \ReflectionProperty(\Magento\Rule\Model\Condition\AbstractCondition::class, '_inputType');
        $property->setAccessible(true);
        $property->setValue($model, 'date');

        $element = $model->getValueElement();
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
