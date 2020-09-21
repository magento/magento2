<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

class AdditionalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea adminhtml
     */
    public function testToHtml()
    {
        $interpreter = $this->createMock(\Magento\Framework\View\Layout\Argument\Interpreter\Options::class);
        /**
         * @var Additional $block
         */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Backend\Block\Widget\Grid\Massaction\Additional::class,
            ['optionsInterpreter' => $interpreter]
        );
        $modelClass = \Magento\Backend\Block\Widget\Grid\Massaction::class;
        $data = [
            'fields' => [
                'field1' => ['type' => 'select', 'values' => $modelClass, 'class' => 'custom_class'],
            ],
        ];
        $block->setData($data);
        $evaluatedValues = [
            ['value' => 'value1', 'label' => 'label 1'],
            ['value' => 'value2', 'label' => 'label 2'],
        ];
        $interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            ['model' => $modelClass]
        )->willReturn(
            $evaluatedValues
        );

        $html = $block->toHtml();
        $this->assertStringMatchesFormat(
            '%acustom_class absolute-advice%avalue="value1"%slabel 1%avalue="value2"%slabel 2%a',
            $html
        );
    }
}
