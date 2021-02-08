<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type;

/**
 * Class \Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\PluginTest
 */
class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $expected
     * @param array $data
     * @dataProvider afterGetOptionArrayDataProvider
     */
    public function testAfterGetOptionArray(array $expected, array $data)
    {
        $moduleManagerMock = $this->createPartialMock(\Magento\Framework\Module\Manager::class, ['isOutputEnabled']);
        $moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with('Magento_ConfigurableProduct')
            ->willReturn($data['is_module_output_enabled']);

        $model = new \Magento\ConfigurableProduct\Model\Product\Type\Plugin($moduleManagerMock);
        $this->assertEquals(
            $expected,
            $model->afterGetOptionArray($data['subject'], $data['result'])
        );
    }

    /**
     * @return array
     */
    public function afterGetOptionArrayDataProvider()
    {
        $productTypeMock = $this->createMock(\Magento\Catalog\Model\Product\Type::class);
        return [
            [
                [
                    'configurable' => true,
                    'not_configurable' => true,
                ],
                [
                    'is_module_output_enabled' => true,
                    'subject' => $productTypeMock,
                    'result' => [
                        'configurable' => true,
                        'not_configurable' => true,
                    ]
                ],
            ],
            [
                [
                    'not_configurable' => true,
                ],
                [
                    'is_module_output_enabled' => false,
                    'subject' => $productTypeMock,
                    'result' => [
                        'configurable' => true,
                        'not_configurable' => true,
                    ]
                ]
            ]
        ];
    }
}
