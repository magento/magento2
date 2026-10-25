<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Test class for \Magento\Rule\Model\Condition\AbstractCondition
 */
namespace Magento\Rule\Model\Condition;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    use MockCreationTrait;
    public function testGetValueElement()
    {
        $layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $context = $objectManager->create(\Magento\Rule\Model\Condition\Context::class, ['layout' => $layoutMock]);

        /** @var \Magento\Rule\Model\Condition\AbstractCondition $model */
        $model = $this->createPartialMockWithReflection(
            \Magento\Rule\Model\Condition\AbstractCondition::class,
            ['getValueElementRenderer']
        );
        // Set the context properties via reflection since constructor was skipped
        // AbstractCondition extracts these from context in its constructor
        $assetRepoProperty = new \ReflectionProperty(\Magento\Rule\Model\Condition\AbstractCondition::class, '_assetRepo');
        $assetRepoProperty->setValue($model, $context->getAssetRepository());

        $localeDateProperty = new \ReflectionProperty(\Magento\Rule\Model\Condition\AbstractCondition::class, '_localeDate');
        $localeDateProperty->setValue($model, $context->getLocaleDate());

        $layoutProperty = new \ReflectionProperty(\Magento\Rule\Model\Condition\AbstractCondition::class, '_layout');
        $layoutProperty->setValue($model, $context->getLayout());
        $editableBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Rule\Block\Editable::class
        );
        $model->expects($this->any())->method('getValueElementRenderer')->willReturn($editableBlock);

        $rule = $this->createMock(\Magento\Rule\Model\AbstractModel::class);
        $rule->expects($this->any())
            ->method('getForm')
            ->willReturn(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Framework\Data\Form::class)
            );
        $model->setRule($rule);

        $property = new \ReflectionProperty(\Magento\Rule\Model\Condition\AbstractCondition::class, '_inputType');
        $property->setValue($model, 'date');

        $element = $model->getValueElement();
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
