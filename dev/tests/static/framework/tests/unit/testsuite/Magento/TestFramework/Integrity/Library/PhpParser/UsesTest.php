<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Check Uses parsing
 */
class UsesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Uses
     */
    protected $uses;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->uses = new Uses();
    }

    /**
     * Covered hasUses method
     *
     * @dataProvider hasUsesDataProvider
     * @test
     *
     * @param array $tokens
     */
    public function testHasUses($tokens)
    {
        foreach ($tokens as $k => $token) {
            $this->uses->parse($token, $k);
        }
        $this->assertTrue($this->uses->hasUses());
    }

    /**
     * Example tokenizer results
     *
     * @return array
     */
    public static function hasUsesDataProvider(): array
    {
        return [
            'simple_php7' => [
                [
                    0 => [T_USE, 'use '],
                    1 => [T_STRING, 'Magento'],
                    2 => [T_NS_SEPARATOR, '\\'],
                    3 => [T_STRING, 'Core'],
                    4 => [T_NS_SEPARATOR, '\\'],
                    5 => [T_STRING, 'Model'],
                    6 => [T_NS_SEPARATOR, '\\'],
                    7 => [T_STRING, 'Object'],
                    8 => ';',
                ],
            ],
            'several_simple_php7' => [
                [
                    0 => [T_USE, 'use '],
                    1 => [T_STRING, 'Magento'],
                    2 => [T_NS_SEPARATOR, '\\'],
                    3 => [T_STRING, 'Core'],
                    4 => [T_NS_SEPARATOR, '\\'],
                    5 => [T_STRING, 'Model'],
                    6 => [T_NS_SEPARATOR, '\\'],
                    7 => [T_STRING, 'Object'],
                    8 => ';',
                    9 => [T_USE, 'use '],
                    10 => [T_STRING, 'Magento'],
                    11 => [T_NS_SEPARATOR, '\\'],
                    12 => [T_STRING, 'Core'],
                    13 => [T_NS_SEPARATOR, '\\'],
                    14 => [T_STRING, 'Model'],
                    15 => [T_NS_SEPARATOR, '\\'],
                    16 => [T_STRING, 'Object2 '],
                    17 => [T_AS, 'as '],
                    18 => [T_STRING, 'OtherObject'],
                    19 => ';',
                ],
            ],
            'several_with_comma_separate_php7' => [
                [
                    0 => [T_USE, 'use '],
                    1 => [T_STRING, 'Magento'],
                    2 => [T_NS_SEPARATOR, '\\'],
                    3 => [T_STRING, 'Core'],
                    4 => [T_NS_SEPARATOR, '\\'],
                    5 => [T_STRING, 'Model'],
                    6 => [T_NS_SEPARATOR, '\\'],
                    7 => [T_STRING, 'Object'],
                    8 => ',',
                    9 => [T_STRING, 'Magento'],
                    10 => [T_NS_SEPARATOR, '\\'],
                    11 => [T_STRING, 'Core'],
                    12 => [T_NS_SEPARATOR, '\\'],
                    13 => [T_STRING, 'Model'],
                    14 => [T_NS_SEPARATOR, '\\'],
                    15 => [T_STRING, 'Object2 '],
                    16 => [T_AS, 'as '],
                    17 => [T_STRING, 'OtherObject'],
                    18 => ';',
                ],
            ],
            'simple' => [
                [
                    0 => [T_USE, 'use '],
                    1 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object'],
                    2 => ';',
                ],
            ],
            'several_simple' => [
                [
                    0 => [T_USE, 'use '],
                    1 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object'],
                    2 => ';',
                    3 => [T_USE, 'use '],
                    4 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object2'],
                    5 => [T_AS, 'as '],
                    6 => [T_STRING, 'OtherObject'],
                    7 => ';',
                ],
            ],
            'several_with_comma_separate' => [
                [
                    0 => [T_USE, 'use '],
                    1 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object'],
                    8 => ',',
                    5 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object2'],
                    16 => [T_AS, 'as '],
                    17 => [T_STRING, 'OtherObject'],
                    18 => ';',
                ],
            ]
        ];
    }

    /**
     * Covered getClassNameWithNamespace for global classes
     *
     * @test
     */
    public function testGetClassNameWithNamespaceForGlobalClass()
    {
        $this->assertEquals(
            '\Magento\Core\Model\Object2',
            $this->uses->getClassNameWithNamespace('\Magento\Core\Model\Object2')
        );
    }

    /**
     * Covered getClassNameWithNamespace
     *
     * @test
     * @dataProvider classNamesDataProvider
     */
    public function testGetClassNameWithNamespace($className, $tokens)
    {
        foreach ($tokens as $k => $token) {
            $this->uses->parse($token, $k);
        }

        $this->assertEquals('Magento\Core\Model\Object2', $this->uses->getClassNameWithNamespace($className));
    }

    /**
     * Return different uses token list and class name
     *
     * @return array
     */
    public static function classNamesDataProvider(): array
    {
        return [
            'class_from_uses_php7' => [
                'Object2',
                [
                    0 => [T_USE, 'use '],
                    1 => [T_STRING, 'Magento'],
                    2 => [T_NS_SEPARATOR, '\\'],
                    3 => [T_STRING, 'Core'],
                    4 => [T_NS_SEPARATOR, '\\'],
                    5 => [T_STRING, 'Model'],
                    6 => [T_NS_SEPARATOR, '\\'],
                    7 => [T_STRING, 'Object2'],
                    8 => ';'
                ],
            ],
            'class_from_uses_with_as_php7' => [
                'ObjectOther',
                [
                    0 => [T_USE, 'use '],
                    1 => [T_STRING, 'Magento'],
                    2 => [T_NS_SEPARATOR, '\\'],
                    3 => [T_STRING, 'Core'],
                    4 => [T_NS_SEPARATOR, '\\'],
                    5 => [T_STRING, 'Model'],
                    6 => [T_NS_SEPARATOR, '\\'],
                    7 => [T_STRING, 'Object2 '],
                    8 => [T_AS, 'as '],
                    9 => [T_STRING, 'ObjectOther'],
                    10 => ';'
                ],
            ],
            'class_from_uses' => [
                'Object2',
                [
                    0 => [T_USE, 'use '],
                    1 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object2'],
                    2 => ';'
                ],
            ],
            'class_from_uses_with_as' => [
                'ObjectOther',
                [
                    0 => [T_USE, 'use '],
                    1 => [T_NAME_QUALIFIED, 'Magento\\Core\\Model\\Object2'],
                    2 => [T_WHITESPACE, ' '],
                    3 => [T_AS, 'as '],
                    4 => [T_STRING, 'ObjectOther'],
                    5 => ';'
                ]
            ]
        ];
    }
}
