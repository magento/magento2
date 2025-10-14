<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Model\Source;

use Magento\Config\Model\Config\Structure\SearchInterface;
use Magento\Config\Model\Config\StructureElementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Variable\Model\Config\Structure\AvailableVariables;
use Magento\Variable\Model\Source\Variables;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Variable\Model\Source\Variables
 */
class VariablesTest extends TestCase
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
    protected $variablesConfigMock;

    /**
     * @var SearchInterface|MockObject
     */
    private $configMock;

    protected function setup(): void
    {
        $this->configMock = $this->getMockBuilder(SearchInterface::class)
            ->addMethods(['getElementByConfigPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $helper = new ObjectManager($this);
        $configVariables = [
            'web' => [
                'web/unsecure/base_url' => '1',
                'web/secure/base_url' => '1'
            ]
        ];
        $this->variablesConfigMock = $this->createMock(AvailableVariables::class);
        $this->variablesConfigMock->expects($this->any())->method('getConfigPaths')->willReturn($configVariables);

        $element1 = $this->getMockBuilder(StructureElementInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLabel'])
            ->getMockForAbstractClass();
        $element2 = clone $element1;
        $groupElement = clone $element1;
        $element1->expects($this->any())->method('getLabel')->willReturn(__('Base URL'));
        $element2->expects($this->any())->method('getLabel')->willReturn(__('Secure Base URL'));
        $groupElement->expects($this->any())->method('getLabel')->willReturn(__('Web'));

        $this->configMock->expects($this->any())->method('getElementByConfigPath')->willReturnMap([
            ['web', $groupElement],
            ['web/unsecure/base_url', $element1],
            ['web/secure/base_url', $element2]
        ]);

        $this->model = $helper->getObject(Variables::class, [
            'configStructure' => $this->configMock,
            'configPaths' => $this->variablesConfigMock
        ]);
    }

    public function testToOptionArrayWithoutGroup()
    {
        $optionArray = $this->model->toOptionArray();
        $this->assertCount(count($this->variablesConfigMock->getConfigPaths()['web']), $optionArray);
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
        $this->assertCount(count($this->variablesConfigMock->getConfigPaths()['web']), $optionArrayValues);
        $expectedResults = $this->getExpectedOptionsResults();
        $index = 0;
        foreach ($optionArray[0]['value'] as $variable) {
            $this->assertEquals($expectedResults[$index]['value'], $variable['value']);
            $this->assertEquals($expectedResults[$index]['label_text'], $variable['label']->getText());
            $index++;
        }
    }

    public function testGetAvailableVars()
    {
        $vars = [
            'web/unsecure/base_url' => '1',
            'web/secure/base_url' => '1'
        ];
        $expected = [
            'web/unsecure/base_url', 'web/secure/base_url'
        ];
        $this->variablesConfigMock->expects($this->any())->method('getFlatConfigPaths')->willReturn($vars);
        $this->assertEquals($expected, $this->model->getAvailableVars());
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
