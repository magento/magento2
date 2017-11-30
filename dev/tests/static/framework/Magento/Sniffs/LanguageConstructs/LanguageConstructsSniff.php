<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\LanguageConstructs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects possible usage of discouraged language constructs. Is not applicable to *.phtml files.
 *
 * Examples:
 * echo 'echo text';
 * print('print text');
 * $string = `back quotes`;
 */
class LanguageConstructsSniff implements Sniff
{
    /**
     * String representation of error.
     *
     * @var string
     */
    protected $errorMessage = 'Use of %s language construct is discouraged.';

    /**
     * String representation of backtick error.
     *
     * @var string
     */
    // @codingStandardsIgnoreLine
    protected $errorMessageBacktick = 'Incorrect usage of back quote string constant. Back quotes should be always inside strings.';

    /**
     * Backtick violation code.
     *
     * @var string
     */
    protected $backtickCode = 'WrongBackQuotesUsage';

    /**
     * Direct output code.
     *
     * @var string
     */
    protected $directOutput = 'DirectOutput';

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_ECHO,
            T_PRINT,
            T_BACKTICK,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['code'] === T_BACKTICK) {
            if ($phpcsFile->findNext(T_BACKTICK, $stackPtr + 1)) {
                return;
            }
            $phpcsFile->addError($this->errorMessageBacktick, $stackPtr, $this->backtickCode);
            return;
        }
        $phpcsFile->addError($this->errorMessage, $stackPtr, $this->directOutput, [$tokens[$stackPtr]['content']]);
    }
}
