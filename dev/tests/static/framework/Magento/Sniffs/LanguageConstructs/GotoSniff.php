<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\LanguageConstructs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects GOTO usage.
 */
class GotoSniff implements Sniff
{
    /**
     * String representation of warning.
     *
     * @var string
     */
    protected $errorMessage = 'Use of goto is discouraged.';

    /**
     * Warning violation code.
     *
     * @var string
     */
    protected $errorCode = 'FoundGoto';

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_GOTO];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $phpcsFile->addError($this->errorMessage, $stackPtr, $this->errorCode);
    }
}
