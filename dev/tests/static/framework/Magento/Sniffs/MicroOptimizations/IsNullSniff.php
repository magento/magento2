<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\MicroOptimizations;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

class IsNullSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @var string
     */
    protected $blacklist = 'is_null';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $sourceFile, $stackPtr)
    {
        $tokens = $sourceFile->getTokens();
        if ($tokens[$stackPtr]['content'] === $this->blacklist) {
            $sourceFile->addError("is_null must be avoided. Use strict comparison instead.", $stackPtr);
        }
    }
}
