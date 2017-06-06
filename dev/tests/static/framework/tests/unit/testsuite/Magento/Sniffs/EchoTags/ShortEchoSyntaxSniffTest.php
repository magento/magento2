<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\EchoTags;

class ShortEchoSyntaxSniffTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHP_CodeSniffer_File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileMock;

    /**
     * @var ShortEchoSyntaxSniff
     */
    private $shortEchoUsageSniff;

    protected function setUp()
    {
        $this->fileMock = $this->getMock(\PHP_CodeSniffer_File::class, [], [], '', false);
        $this->shortEchoUsageSniff = new ShortEchoSyntaxSniff();
    }

    /**
     * @param string $file
     * @param int $incorrectUsages
     * @dataProvider processDataProvider
     */
    public function testEchoTagSniff($file, $stackPtr, $incorrectUsages)
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/' . $file);
        $tokens = $this->tokenizeString($fileContent);

        $this->fileMock->expects($this->any())
            ->method('findNext')
            ->with([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], $stackPtr + 1, null, true)
            ->willReturn($stackPtr + 1);

        $this->fileMock->expects($this->once())
            ->method('getTokens')
            ->willReturn($tokens);

        $this->fileMock->expects($this->exactly($incorrectUsages))
            ->method('addError')
            ->with('Short echo tag syntax must be used; expected "<?=" but found "<?php echo"');

        $this->shortEchoUsageSniff->process($this->fileMock, $stackPtr);
    }

    /**
     * Get tokens for a string
     *
     * @param string $fileContent
     * @return array
     */
    private function tokenizeString($fileContent)
    {
        $tokens = token_get_all($fileContent);
        $snifferTokens = [];
        for ($i = 0; $i < count($tokens); $i++) {
            $code = is_array($tokens[$i]) ? $tokens[$i][0] : $tokens[$i];
            $content = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
            $snifferTokens[$i]['code'] = $code;
            $snifferTokens[$i]['content'] = $content;
        }
        return $snifferTokens;
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['incorrect_echotag.phtml', 1, 1],
            ['correct_noecho.phtml', 1, 0],
            ['correct_echotag.phtml', 1, 0]
        ];
    }
}
