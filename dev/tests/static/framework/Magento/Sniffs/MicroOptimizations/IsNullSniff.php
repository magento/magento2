<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\MicroOptimizations;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class IsNullSniff implements Sniff
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
    public function process(File $sourceFile, $stackPtr)
    {
        $tokens = $sourceFile->getTokens();
        if ($tokens[$stackPtr]['content'] === $this->blacklist) {
            $sourceFile->addError(
                "is_null must be avoided. Use strict comparison instead.",
                $stackPtr,
                'IsNullUsage'
            );
        }
    }
}
