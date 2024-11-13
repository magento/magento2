<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Block\Product\Widget;

/**
 * Test for \Magento\CatalogWidget\Block\Product\Widget\Conditions
 */
class ConditionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogWidget\Block\Product\Widget\Conditions
     */
    protected $block;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->block = $this->objectManager->create(
            \Magento\CatalogWidget\Block\Product\Widget\Conditions::class
        )->setArea('adminhtml');
    }

    public function testRender()
    {
        $form = $this->objectManager->create(\Magento\Framework\Data\Form::class);

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $container */
        $container = $this->objectManager->create(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $container->setForm($form);
        $container->setData([
            'html_id' => 'options_fieldset67a77e971a7c331b6eaefcaf2f596097',
            'type' => 'fieldset',
        ]);

        /** @var \Magento\Framework\Data\Form\Element\Text $element */
        $element = $this->objectManager->create(\Magento\Framework\Data\Form\Element\Text::class);
        $data = [
            'name' => 'parameters[condition]',
            'label' => 'Conditions',
            'required' => 1,
            'class' => 'widget-option input-text required-entry',
            'note' => '',
            'value' => null,
            'type' => 'text',
            'ext_type' => 'textfield',
            'container' => $container,
            'container_id' => '',
            'html_id' => 'options_fieldset67a77e971a7c331b6eaefcaf2f596097_condition',
        ];
        $element->setData($data);
        $element->setContainer($container);
        $element->setForm($form);

        $result = $this->block->render($element);

        /* Assert HTML contains form elements */
        $this->assertStringContainsString('name="parameters[conditions][1][type]"', $result);
        $this->assertStringContainsString('name="parameters[conditions][1][value]"', $result);
        /* Assert HTML contains child url */
        $this->assertStringContainsString(
            'catalog_widget/product_widget/conditions/form/options_fieldset67a77e971a7c331b6eaefcaf2f596097',
            $result
        );
        /* Assert HTML contains html id */
        $this->assertStringContainsString('window.options_fieldset67a77e971a7c331b6eaefcaf2f596097', $result);
        /* Assert HTML contains required JS code */
        $this->assertStringContainsString("VarienRulesForm(
        'options_fieldset67a77e971a7c331b6eaefcaf2f596097", $result);
    }
}
