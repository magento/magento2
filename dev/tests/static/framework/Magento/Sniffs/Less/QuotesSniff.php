<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class QuotesSniff
 *
 * Ensure that single quotes are used
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#quotes
 *
 */
class QuotesSniff implements PHP_CodeSniffer_Sniff
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
        return [T_CONSTANT_ENCAPSED_STRING];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (false !== strpos($tokens[$stackPtr]['content'], '"')) {
            $phpcsFile->addError('Use single quotes', $stackPtr, 'DoubleQuotes');
        }
    }
}
