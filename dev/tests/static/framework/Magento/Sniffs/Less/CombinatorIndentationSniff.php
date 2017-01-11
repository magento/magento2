<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class CombinatorIndentationSniff
 *
 * Ensure that spaces are used before and after combinators
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#combinator-indents
 *
 */
class CombinatorIndentationSniff implements PHP_CodeSniffer_Sniff
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
        return [T_PLUS];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $prevPtr = $stackPtr - 1;
        $nextPtr = $stackPtr + 1;

        if (($tokens[$prevPtr]['code'] !== T_WHITESPACE) || ($tokens[$nextPtr]['code'] !== T_WHITESPACE)) {
            $phpcsFile->addError('Spaces should be before and after combinators', $stackPtr, 'NoSpaces');
        }
    }
}
