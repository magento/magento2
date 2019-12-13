<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option;

/**
 * Test for customizable product option with "Text" type
 */
class TextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Text
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            Text::class
        );
    }

    /**
     * Check if newline symbols are normalized in option value
     *
     * @dataProvider optionValueDataProvider
     * @param array $productOptionData
     * @param string $optionValue
     * @param string $expectedOptionValue
     */
    public function testNormalizeNewlineSymbols(
        array $productOptionData,
        string $optionValue,
        string $expectedOptionValue
    ) {
        $productOption = $this->objectManager->create(
            Option::class,
            ['data' => $productOptionData]
        );

        $this->model->setOption($productOption);
        $this->model->setUserValue($optionValue);
        $this->model->validateUserValue([]);

        $this->assertSame($expectedOptionValue, $this->model->getUserValue());
    }

    /**
     * Data provider for testNormalizeNewlineSymbols
     *
     * @return array
     */
    public function optionValueDataProvider()
    {
        return [
            [
                // $productOptionData
                ['id' => 11, 'type' => 'area'],
                // $optionValue
                'string string',
                // $expectedOptionValue
                'string string'
            ],
            [
                // $productOptionData
                ['id' => 11, 'type' => 'area'],
                // $optionValue
                "string \r\n string",
                // $expectedOptionValue
                "string \n string"
            ],
            [
                // $productOptionData
                ['id' => 11, 'type' => 'area'],
                // $optionValue
                "string \n\r string",
                // $expectedOptionValue
                "string \n string"
            ],
            [
                // $productOptionData
                ['id' => 11, 'type' => 'area'],
                // $optionValue
                "string \r string",
                // $expectedOptionValue
                "string \n string"
            ]
        ];
    }
}
