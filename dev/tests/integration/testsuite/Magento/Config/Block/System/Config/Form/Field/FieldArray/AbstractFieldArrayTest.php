<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Block\System\Config\Form\Field\FieldArray;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class AbstractFieldArrayTest extends TestCase
{
    /**
     * FieldArray template must mark __empty sentinel as disableable for dependence JS.
     */
    public function testEmptySentinelHasDisableableClass(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var LayoutInterface $layout */
        $layout = $objectManager->create(LayoutInterface::class);
        /** @var TestFieldArray $block */
        $block = $layout->createBlock(TestFieldArray::class);
        /** @var ElementFactory $elementFactory */
        $elementFactory = $objectManager->get(ElementFactory::class);
        /** @var Form $form */
        $form = $objectManager->create(Form::class);

        $element = $elementFactory->create('textarea');
        $element->setForm($form);
        $element->setId('test_field_array');
        $element->setName('groups[general][fields][items][value]');
        $element->setValue([]);
        $element->setLabel('Items');

        $html = $block->render($element);

        $this->assertStringContainsString(
            '[__empty]',
            $html,
            'FieldArray must render the __empty sentinel input'
        );
        $this->assertStringContainsString(
            'disableable',
            $html,
            'The __empty sentinel must include the disableable class for FormElementDependenceController'
        );

        // Ensure disableable is on the __empty input specifically (same tag)
        $this->assertMatchesRegularExpression(
            '/<input\b(?=[^>]*\bdisableable\b)(?=[^>]*\[__empty\])[^>]*>/i',
            $html,
            'disableable class must be on the __empty input element'
        );
    }
}
