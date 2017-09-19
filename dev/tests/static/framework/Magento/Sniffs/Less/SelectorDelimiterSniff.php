<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class SelectorDelimiterSniff
 *
 * Ensure that a line break exists after each selector delimiter.
 * No spaces should be before or after delimiters.
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#selector-delimiters
 *
 */
class SelectorDelimiterSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_COMMA];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check that there's no spaces before delimiter
        if ($tokens[$stackPtr - 1]['code'] === T_WHITESPACE) {
            $phpcsFile->addError('Spaces should not be before delimiter', $stackPtr - 1, 'SpacesBeforeDelimiter');
        }

        $this->validateParenthesis($phpcsFile, $stackPtr, $tokens);
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @return void
     */
    private function validateParenthesis(File $phpcsFile, $stackPtr, array $tokens)
    {
        $nextPtr = $stackPtr + 1;

        $nextClassPtr = $phpcsFile->findNext(T_STRING_CONCAT, $nextPtr);
        $nextOpenBrace = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $nextPtr);

        if ($nextClassPtr === false || $nextOpenBrace === false) {
            return;
        }

        $stackLine = $tokens[$stackPtr]['line'];
        $nextClassLine = $tokens[$nextPtr]['line'];
        $nextOpenBraceLine = $tokens[$nextOpenBrace]['line'];

        // Check that each class declaration goes from new line
        if (($stackLine === $nextClassLine) && ($stackLine === $nextOpenBraceLine)) {
            $prevParenthesis = $phpcsFile->findPrevious(T_OPEN_PARENTHESIS, $stackPtr);
            $nextParenthesis = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr);

            if ((false !== $prevParenthesis) && (false !== $nextParenthesis)
                && ($tokens[$prevParenthesis]['line'] === $tokens[$stackPtr]['line'])
                && ($tokens[$nextParenthesis]['line'] === $tokens[$stackPtr]['line'])
            ) {
                return;
            }

            $error = 'Add a line break after each selector delimiter';
            $phpcsFile->addError($error, $nextOpenBrace, 'LineBreakAfterDelimiter');
        }
    }
}
