<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Translation;

use PHP_CodeSniffer_File;

/**
 * Make sure that constants are not used as the first argument of translation function.
 */
class ConstantUsageSniff extends \Generic_Sniffs_Files_LineLengthSniff
{
    /**
     * Having previous line content allows to process multi-line declaration.
     *
     * @var string
     */
    protected $previousLineContent = '';

    /**
     * {@inheritdoc}
     */
    protected function checkLineLength(\PHP_CodeSniffer_File $phpcsFile, $stackPtr, $lineContent)
    {
        $previousLineRegexp = '~__\($|Phrase\($~';
        $currentLineRegexp = '~__\(.+\)|Phrase\(.+\)~';
        $currentLineMatch = preg_match($currentLineRegexp, $lineContent) !== 0;
        $previousLineMatch = preg_match($previousLineRegexp, $this->previousLineContent) !== 0;
        $this->previousLineContent = $lineContent;
        $error = 'Constants are not allowed as the first argument of translation function, use string literal instead';
        $constantRegexp = '[^\'"]+::[A-Z_0-9]+.*';
        if ($currentLineMatch) {
            $variableRegexp = "~__\({$constantRegexp}\)|Phrase\({$constantRegexp}\)~";
            if (preg_match($variableRegexp, $lineContent) !== 0) {
                $phpcsFile->addError($error, $stackPtr, 'VariableTranslation');
            }
        } else if ($previousLineMatch) {
            $variableRegexp = "~^\s+{$constantRegexp}~";
            if (preg_match($variableRegexp, $lineContent) !== 0) {
                $phpcsFile->addError($error, $stackPtr, 'VariableTranslation');
            }
        }
    }
}
