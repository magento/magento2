<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class AvoidIdSniff
 *
 * Ensure that id selector is not used
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#types
 *
 */
class AvoidIdSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * @var array
     */
    private $symbolsBeforeId = [
        TokenizerSymbolsInterface::INDENT_SPACES,
        TokenizerSymbolsInterface::NEW_LINE,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_HASH];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (T_WHITESPACE === $tokens[$stackPtr - 1]['code']
            && in_array($tokens[$stackPtr - 1]['content'], $this->symbolsBeforeId)
        ) {
            $phpcsFile->addError('Id selector is used', $stackPtr);
        }
    }
}
