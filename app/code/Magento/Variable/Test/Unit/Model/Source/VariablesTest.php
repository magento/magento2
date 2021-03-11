<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Test\Unit\Model\Source;

/**
 * Unit test for Magento\Variable\Model\Source\Variables
 */
class VariablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Variables model
     *
     * @var \Magento\Variable\Model\Source\Variables
     */
    protected $model;

    /**
     * Config variables
     *
     * @var array
     */
    protected $configVariables;

    /**
     * @var \Magento\Config\Model\Config\Structure\SearchInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    protected function setup(): void
    {
        $this->configMock = $this->getMockBuilder(\Magento\Config\Model\Config\Structure\SearchInterface::class)
            ->setMethods(['getElementByConfigPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configVariables = [
            'web' => [
                'web/unsecure/base_url' => '1',
                'web/secure/base_url' => '1'
            ]
        ];

        $element1 = $this->getMockBuilder(\Magento\Config\Model\Config\StructureElementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLabel'])
            ->getMockForAbstractClass();
        $element2 = clone $element1;
        $groupElement = clone $element1;
        $element1->expects($this->once())->method('getLabel')->willReturn(__('Base URL'));
        $element2->expects($this->once())->method('getLabel')->willReturn(__('Secure Base URL'));
        $groupElement->expects($this->once())->method('getLabel')->willReturn(__('Web'));

        $this->configMock->expects($this->any())->method('getElementByConfigPath')->willReturnMap([
            ['web', $groupElement],
            ['web/unsecure/base_url', $element1],
            ['web/secure/base_url', $element2]
        ]);

        $this->model = $helper->getObject(\Magento\Variable\Model\Source\Variables::class, [
            'configStructure' => $this->configMock,
            'configPaths' => $this->configVariables
        ]);
    }

    public function testToOptionArrayWithoutGroup()
    {
        $optionArray = $this->model->toOptionArray();
        $this->assertEquals(count($this->configVariables['web']), count($optionArray));
        $expectedResults = $this->getExpectedOptionsResults();
        $index = 0;
        foreach ($optionArray as $variable) {
            $this->assertEquals($expectedResults[$index]['value'], $variable['value']);
            $this->assertEquals($expectedResults[$index]['label_text'], $variable['label']->getText());
            $index++;
        }
    }

    public function testToOptionArrayWithGroup()
    {
        $optionArray = $this->model->toOptionArray(true);
        $this->assertEquals('Web', $optionArray[0]['label']);
        $optionArrayValues = $optionArray[0]['value'];
        $this->assertEquals(count($this->configVariables['web']), count($optionArrayValues));
        $expectedResults = $this->getExpectedOptionsResults();
        $index = 0;
        foreach ($optionArray[0]['value'] as $variable) {
            $this->assertEquals($expectedResults[$index]['value'], $variable['value']);
            $this->assertEquals($expectedResults[$index]['label_text'], $variable['label']->getText());
            $index++;
        }
    }

    /**
     * Get expected results for options array
     */
    private function getExpectedOptionsResults()
    {
        return [
            [
                'value' => '{{config path="web/unsecure/base_url"}}',
                'label_text' => 'Base URL'
            ],
            [
                'value' => '{{config path="web/secure/base_url"}}',
                'label_text' => 'Secure Base URL'
            ],
        ];
    }
}
