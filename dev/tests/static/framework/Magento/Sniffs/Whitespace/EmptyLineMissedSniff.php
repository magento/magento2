<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Whitespace;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class EmptyLineMissedSniff
 */
class EmptyLineMissedSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_DOC_COMMENT];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($this->doCheck($phpcsFile, $stackPtr, $tokens)) {
            $previous = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
            if ($tokens[$stackPtr]['line'] - $tokens[$previous]['line'] < 2) {
                $error = 'Empty line missed';
                $phpcsFile->addError($error, $stackPtr, '', null);
            }
        }
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @return bool
     */
    private function doCheck(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        $result = false;
        if ($phpcsFile->hasCondition($stackPtr, T_CLASS) || $phpcsFile->hasCondition($stackPtr, T_INTERFACE)) {
            $result = true;
        }

        if ($phpcsFile->hasCondition($stackPtr, T_FUNCTION)) {
            $result = false;
        }
        $previous = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        if ($tokens[$previous]['type'] === 'T_OPEN_CURLY_BRACKET') {
            $result = false;
        }

        if (strpos($tokens[$stackPtr]['content'], '/**') === false) {
            $result = false;
        }

        return $result;
    }
}
