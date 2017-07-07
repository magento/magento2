<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Arrays;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

class ShortArraySyntaxSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_ARRAY];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $sourceFile, $stackPtr)
    {
        $sourceFile->addError('Short array syntax must be used; expected "[]" but found "array()"', $stackPtr);
    }
}
