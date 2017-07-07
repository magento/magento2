<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class PropertiesLineBreakSniff
 *
 * Start each property declaration in a new line
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#properties-line-break
 *
 */
class PropertiesLineBreakSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_SEMICOLON];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $prevPtr = $phpcsFile->findPrevious(T_SEMICOLON, ($stackPtr - 1));
        if (false === $prevPtr) {
            return;
        }

        if ($tokens[$prevPtr]['line'] === $tokens[$stackPtr]['line']) {
            $error = 'Each propery must be on a line by itself';
            $phpcsFile->addError($error, $stackPtr, 'SameLine');
        }
    }
}
