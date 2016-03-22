<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_Tokens;

/**
 * Class ColonSpacingSniff
 *
 * Ensure that single quotes are used
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#properties-colon-indents
 *
 */
class ColonSpacingSniff implements PHP_CodeSniffer_Sniff
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
        return [T_COLON];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($this->needValidateSpaces($phpcsFile, $stackPtr, $tokens)) {
            $this->validateSpaces($phpcsFile, $stackPtr, $tokens);
        }
    }

    /**
     * Check is it need to check spaces
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     *
     * @return bool
     */
    private function needValidateSpaces(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        $nextSemicolon = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);

        if (false === $nextSemicolon
            || ($tokens[$nextSemicolon]['line'] !== $tokens[$stackPtr]['line'])
            || TokenizerSymbolsInterface::BITWISE_AND === $tokens[$stackPtr - 1]['content']
        ) {
            return false;
        }

        $prev = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true);
        if ($tokens[$prev]['code'] !== T_STYLE) {
            // The colon is not part of a style definition.
            return false;
        }

        if ($tokens[$prev]['content'] === 'progid') {
            // Special case for IE filters.
            return false;
        }

        return true;
    }

    /**
     * Validate Colon Spacing according to requirements
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     *
     * @return void
     */
    private function validateSpaces(PHP_CodeSniffer_File $phpcsFile, $stackPtr, array $tokens)
    {
        if (T_WHITESPACE === $tokens[($stackPtr - 1)]['code']) {
            $phpcsFile->addError('There must be no space before a colon in a style definition', $stackPtr, 'Before');
        }

        if (T_WHITESPACE !== $tokens[($stackPtr + 1)]['code']) {
            $phpcsFile->addError('Expected 1 space after colon in style definition; 0 found', $stackPtr, 'NoneAfter');
        } else {
            $content = $tokens[($stackPtr + 1)]['content'];
            if (false === strpos($content, $phpcsFile->eolChar)) {
                $length  = strlen($content);
                if ($length !== 1) {
                    $error = 'Expected 1 space after colon in style definition; %s found';
                    $phpcsFile->addError($error, $stackPtr, 'After');
                }
            } else {
                $error = 'Expected 1 space after colon in style definition; newline found';
                $phpcsFile->addError($error, $stackPtr, 'AfterNewline');
            }
        }
    }
}
