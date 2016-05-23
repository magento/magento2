<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class ImportantPropertySniff
 *
 * Ensure that single quotes are used
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#important-property
 *
 */
class ImportantPropertySniff implements PHP_CodeSniffer_Sniff
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
        return [T_BOOLEAN_NOT];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Will be implemented in MAGETWO-49778
        //$phpcsFile->addWarning('!important is used', $stackPtr, '!ImportantIsUsed');

        if (($tokens[$stackPtr + 1]['content'] === 'important')
            && ($tokens[$stackPtr - 1]['content'] !== TokenizerSymbolsInterface::WHITESPACE)
        ) {
            $phpcsFile->addError('Space before !important is missing', $stackPtr, 'NoSpace');
        }
    }
}
