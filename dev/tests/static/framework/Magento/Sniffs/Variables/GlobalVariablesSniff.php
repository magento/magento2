<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sniffs\Variables;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff prohibiting usage of global variables.
 */
class GlobalVariablesSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (preg_match('/^\$[_A-Z0-9]+$/', $tokens[$stackPtr]['content'])) {
            $phpcsFile->addError(
                'Usage of global variables is not allowed: ' . $tokens[$stackPtr]['content'],
                $stackPtr,
                'ERROR'
            );
            return;
        }
    }
}
