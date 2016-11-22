<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Translation;

/**
 * Make sure that constants are not used as the first argument of translation function.
 */
class ConstantUsageSniff implements \PHP_CodeSniffer_Sniff
{
    /**
     * Having previous line content allows to process multi-line declaration.
     *
     * @var string
     */
    protected $previousLineContent = '';

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_OPEN_TAG];

    }

    /**
     * Copied from \Generic_Sniffs_Files_LineLengthSniff, minor changes made
     *
     * {@inheritDoc}
     */
    public function process(\PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Make sure this is the first open tag
        $previousOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1));
        if ($previousOpenTag !== false) {
            return;
        }

        $tokenCount = 0;
        $currentLineContent = '';
        $currentLine = 1;

        for (; $tokenCount < $phpcsFile->numTokens; $tokenCount++) {
            if ($tokens[$tokenCount]['line'] === $currentLine) {
                $currentLineContent .= $tokens[$tokenCount]['content'];
            } else {
                $this->checkIfFirstArgumentConstant($phpcsFile, ($tokenCount - 1), $currentLineContent);
                $currentLineContent = $tokens[$tokenCount]['content'];
                $currentLine++;
            }
        }

        $this->checkIfFirstArgumentConstant($phpcsFile, ($tokenCount - 1), $currentLineContent);
    }

    /**
     * Checks if first argument of \Magento\Framework\Phrase or translation function is a constant
     *
     * @param \PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @param string $lineContent
     * @return void
     */
    private function checkIfFirstArgumentConstant(
        \PHP_CodeSniffer_File $phpcsFile,
        $stackPtr,
        $lineContent
    ) {
        $previousLineRegexp = '/(__|Phrase)\($/im';
        $currentLineRegexp = '/(__|Phrase)\(.+\)/';
        $currentLineMatch = preg_match($currentLineRegexp, $lineContent) !== 0;
        $previousLineMatch = preg_match($previousLineRegexp, $this->previousLineContent) !== 0;
        $this->previousLineContent = $lineContent;
        $error = 'Constants are not allowed as the first argument of translation function, use string literal instead';
        $constantRegexp = '[^\$\'"]+::[A-Z_0-9]+.*';
        if ($currentLineMatch) {
            $variableRegexp = "/(__|Phrase)\({$constantRegexp}\)/";
            if (preg_match($variableRegexp, $lineContent) !== 0) {
                $phpcsFile->addError($error, $stackPtr, 'VariableTranslation');
            }
        } else if ($previousLineMatch) {
            $variableRegexp = "/^{$constantRegexp}/";
            if (preg_match($variableRegexp, $lineContent) !== 0) {
                $phpcsFile->addError($error, $stackPtr, 'VariableTranslation');
            }
        }
    }
}
