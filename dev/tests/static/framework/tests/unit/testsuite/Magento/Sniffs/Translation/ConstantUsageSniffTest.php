<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Translation;

class ConstantUsageSniffTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHP_CodeSniffer_File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileMock;

    /**
     * @var ConstantUsageSniff
     */
    private $constantUsageSniff;

    protected function setUp()
    {
        $this->fileMock = $this->getMock(\PHP_CodeSniffer_File::class, [], [], '', false);
        $this->constantUsageSniff = new ConstantUsageSniff();
    }

    /**
     * @param string $line
     * @dataProvider checkLineLengthCorrectArguments
     */
    public function testCheckLineLengthCorrectArguments($line)
    {
        $this->fileMock->expects($this->never())
            ->method('addError');
        $this->checkLineLength(10, $line);
    }

    /**
     * @return array
     */
    public function checkLineLengthCorrectArguments()
    {
        return [
            [
                '__($item)'
            ],
            [
                '__($item[ConfigConverter::KEY_TITLE])'
            ],
            [
                '__($item[\'value\'])'
            ],
            [
                '__($item->getValue())'
            ],
            [
                'Phrase($item)'
            ],
            [
                'Phrase($item[ConfigConverter::KEY_TITLE])'
            ],
            [
                'Phrase($item[\'value\'])'
            ],
            [
                'Phrase($item->getValue())'
            ],
            [
                '\Magento\Framework\Phrase($item)'
            ]
        ];
    }

    /**
     * @param string $line
     * @dataProvider checkLineLengthIncorrectArguments
     */
    public function testCheckLineLengthIncorrectArguments($line)
    {
        $lineNumber = 10;
        $this->fileMock->expects($this->once())
            ->method('addError')
            ->with(
                'Constants are not allowed as the first argument of translation function, use string literal instead',
                $lineNumber,
                'VariableTranslation'
            );
        $this->checkLineLength($lineNumber, $line);
    }

    /**
     * @return array
     */
    public function checkLineLengthIncorrectArguments()
    {
        return [
            [
                '$item[ConfigConverter::KEY_TITLE] = __(Converter::KEY_TITLE)'
            ],
            [
                '$item[ConfigConverter::KEY_TITLE] = __(self::KEY_TITLE)'
            ],
            [
                '$item[ConfigConverter::KEY_TITLE] = __(\Magento\Support\Model\Report\Config\Converter::KEY_TITLE)'
            ],
            [
                'Phrase(Converter::KEY_TITLE)'
            ],
            [
                'Phrase(self::KEY_TITLE)'
            ],
            [
                'Phrase(\Magento\Support\Model\Report\Config\Converter::KEY_TITLE)'
            ],
            [
                '\Magento\Framework\Phrase(Converter::KEY_TITLE)'
            ]
        ];
    }

    /**
     * Call checkLineLength method
     *
     * @param int $lineNumber
     * @param string $line
     */
    private function checkLineLength($lineNumber, $line)
    {
        $reflectionMethod = new \ReflectionMethod(ConstantUsageSniff::class, 'checkLineLength');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->constantUsageSniff, $this->fileMock, $lineNumber, $line);
    }
}
