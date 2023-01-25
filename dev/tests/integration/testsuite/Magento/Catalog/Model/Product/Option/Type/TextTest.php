<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for customizable product option with "Text" type
 */
class TextTest extends TestCase
{
    const STUB_OPTION_DATA = ['id' => 11, 'type' => 'area'];

    /**
     * @var Text
     */
    protected $optionText;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->optionText = $this->objectManager->create(Text::class);
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

        $this->optionText->setOption($productOption);
        $this->optionText->setUserValue($optionValue);
        $this->optionText->validateUserValue([]);

        $this->assertSame($expectedOptionValue, $this->optionText->getUserValue());
    }

    /**
     * Data provider for testNormalizeNewlineSymbols
     *
     * @return array
     */
    public function optionValueDataProvider()
    {
        return [
            [self::STUB_OPTION_DATA, 'string string', 'string string'],
            [self::STUB_OPTION_DATA, "string \r\n string", "string \n string"],
            [self::STUB_OPTION_DATA, "string \n\r string", "string \n string"],
            [self::STUB_OPTION_DATA, "string \r string", "string \n string"]
        ];
    }
}
