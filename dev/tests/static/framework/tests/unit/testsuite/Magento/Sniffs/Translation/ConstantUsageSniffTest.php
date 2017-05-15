<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param string $file
     * @param int $numIncorrectUsages
     * @dataProvider processDataProvider
     */
    public function testProcessIncorrectArguments($file, $numIncorrectUsages)
    {
        $stackPtr = 10;
        $fileContent = file_get_contents(__DIR__ . '/_files/' . $file);
        $tokens = $this->tokenizeString($fileContent);
        $this->fileMock->expects($this->once())
            ->method('findPrevious')
            ->with(
                T_OPEN_TAG,
                $stackPtr - 1
            )
            ->willReturn(false);
        $this->fileMock->expects($this->once())
            ->method('getTokens')
            ->willReturn($tokens);
        $this->fileMock->numTokens = count($tokens);
        $this->fileMock->expects($this->exactly($numIncorrectUsages))
            ->method('addError')
            ->with(
                'Constants are not allowed as the first argument of translation function, use string literal instead',
                $this->anything(),
                'VariableTranslation'
            );
        $this->constantUsageSniff->process($this->fileMock, $stackPtr);
    }

    /**
     * Get tokens for a string
     *
     * @param string $fileContent
     * @return array
     */
    private function tokenizeString($fileContent)
    {
        $lineNumber = 1;
        $tokens = token_get_all($fileContent);
        $snifferTokens = [];
        for ($i = 0; $i < count($tokens); $i++) {
            $content = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
            $snifferTokens[$i]['line'] = $lineNumber;
            $snifferTokens[$i]['content'] = $content;
            $trimmedContent = trim($content, ' ');
            if ($trimmedContent == PHP_EOL || $trimmedContent == PHP_EOL . PHP_EOL) {
                $lineNumber++;
            }
        }
        return $snifferTokens;
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'incorrect_arguments.txt',
                9
            ],
            [
                'correct_arguments.txt',
                0
            ]
        ];
    }
}
