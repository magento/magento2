<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class SemicolonSpacingSniff
 *
 * Property should have a semicolon at the end of line
 *
 * @link https://devdocs.magento.com/guides/v2.3/coding-standards/code-standard-less.html#end-of-the-property-line
 */
class SemicolonSpacingSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * Skip symbols that can be detected by sniffer incorrectly
     *
     * @var array
     */
    private $styleSymbolsToSkip = [
        TokenizerSymbolsInterface::BITWISE_AND,
        TokenizerSymbolsInterface::COLON,
        TokenizerSymbolsInterface::OPEN_PARENTHESIS,
        TokenizerSymbolsInterface::CLOSE_PARENTHESIS,
    ];

    /** Skip codes that can be detected by sniffer incorrectly
     *
     * @var array
     */
    private $styleCodesToSkip = [T_ASPERAND, T_COLON, T_OPEN_PARENTHESIS, T_CLOSE_PARENTHESIS];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_STYLE];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (in_array($tokens[$stackPtr]['content'], $this->styleSymbolsToSkip)) {
            return;
        }

        $semicolonPtr = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
        if ($tokens[$semicolonPtr]['line'] !== $tokens[$stackPtr]['line']) {
            $semicolonPtr = $phpcsFile->findNext(T_STYLE, ($stackPtr + 1), null, false, ";");
        }

        $this->validateSemicolon($phpcsFile, $stackPtr, $tokens, $semicolonPtr);
        $this->validateSpaces($phpcsFile, $stackPtr, $tokens, $semicolonPtr);
    }

    /**
     * Semicolon validation.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @param int $semicolonPtr
     * @return void
     */
    private function validateSemicolon(File $phpcsFile, $stackPtr, array $tokens, $semicolonPtr)
    {
        if ((false === $semicolonPtr || $tokens[$semicolonPtr]['line'] !== $tokens[$stackPtr]['line'])
            && (isset($tokens[$stackPtr - 1]) && !in_array($tokens[$stackPtr - 1]['code'], $this->styleCodesToSkip))
            && (T_COLON !== $tokens[$stackPtr + 1]['code'])
        ) {
            $error = 'Style definitions must end with a semicolon';
            $phpcsFile->addError($error, $stackPtr, 'NotAtEnd');
        }
    }

    /**
     * Spaces validation.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @param int $semicolonPtr
     * @return void
     */
    private function validateSpaces(File $phpcsFile, $stackPtr, array $tokens, $semicolonPtr)
    {
        if (!isset($tokens[($semicolonPtr - 1)])) {
            return;
        }

        if ($tokens[($semicolonPtr - 1)]['code'] === T_WHITESPACE) {
            $length  = strlen($tokens[($semicolonPtr - 1)]['content']);
            $error = 'Expected 0 spaces before semicolon in style definition; %s found';
            $data  = [$length];
            $phpcsFile->addError($error, $stackPtr, 'SpaceFound', $data);
        }
    }
}
