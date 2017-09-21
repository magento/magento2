<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Whitespace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class MultipleEmptyLinesSniff
 */
class MultipleEmptyLinesSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_WHITESPACE];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($phpcsFile->hasCondition($stackPtr, T_FUNCTION)
            || $phpcsFile->hasCondition($stackPtr, T_CLASS)
            || $phpcsFile->hasCondition($stackPtr, T_INTERFACE)
        ) {
            if ($tokens[($stackPtr - 1)]['line'] < $tokens[$stackPtr]['line']
                && $tokens[($stackPtr - 2)]['line'] === $tokens[($stackPtr - 1)]['line']
            ) {
                // This is an empty line and the line before this one is not
                // empty, so this could be the start of a multiple empty line block
                $next  = $phpcsFile->findNext(T_WHITESPACE, $stackPtr, null, true);
                $lines = $tokens[$next]['line'] - $tokens[$stackPtr]['line'];
                if ($lines > 1) {
                    $error = 'Code must not contain multiple empty lines in a row; found %s empty lines';
                    $data  = [$lines];
                    $phpcsFile->addError($error, $stackPtr, 'MultipleEmptyLines', $data);
                }
            }
        }
    }
}
