<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Class BracesFormattingSniff
 *
 * Ensure there is a single blank line after the closing brace of a class definition
 *
 * @see Squiz_Sniffs_CSS_ClassDefinitionClosingBraceSpaceSniff
 * @link https://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#braces
 */
class BracesFormattingSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (T_OPEN_CURLY_BRACKET === $tokens[$stackPtr]['code']) {
            if (TokenizerSymbolsInterface::WHITESPACE !== $tokens[$stackPtr - 1]['content']) {
                $phpcsFile->addError('Space before opening brace is missing', $stackPtr, 'SpacingBeforeOpen');
            }

            return;
        }

        $next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($next === false) {
            return;
        }

        if (!in_array($tokens[$next]['code'], [T_CLOSE_TAG, T_CLOSE_CURLY_BRACKET])) {
            $found = (($tokens[$next]['line'] - $tokens[$stackPtr]['line']) - 1);
            if ($found !== 1) {
                $error = 'Expected one blank line after closing brace of class definition; %s found';
                $data = [$found];
                // Will be implemented in MAGETWO-49778
                //$phpcsFile->addWarning($error, $stackPtr, 'SpacingAfterClose', $data);
            }
        }

        // Ignore nested style definitions from here on. The spacing before the closing brace
        // (a single blank line) will be enforced by the above check, which ensures there is a
        // blank line after the last nested class.
        $found = $phpcsFile->findPrevious(
            T_CLOSE_CURLY_BRACKET,
            ($stackPtr - 1),
            $tokens[$stackPtr]['bracket_opener']
        );

        if ($found !== false) {
            return;
        }

        $prev = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
        if ($prev !== false && $tokens[$prev]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
            $num = ($tokens[$stackPtr]['line'] - $tokens[$prev]['line'] - 1);
            $error = 'Expected 0 blank lines before closing brace of class definition; %s found';
            $data = [$num];
            $phpcsFile->addError($error, $stackPtr, 'SpacingBeforeClose', $data);
        }
    }
}
