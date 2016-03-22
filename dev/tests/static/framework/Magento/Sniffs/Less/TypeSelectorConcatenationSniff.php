<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class TypeSelectorConcatenation
 *
 * Ensure that selector in one line, concatenation is not used
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#formatting-1
 *
 */
class TypeSelectorConcatenationSniff implements PHP_CodeSniffer_Sniff
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
    private $symbolsBeforeConcat = [
        TokenizerSymbolsInterface::INDENT_SPACES,
        TokenizerSymbolsInterface::NEW_LINE,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_BITWISE_AND];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (0 === strpos($tokens[$stackPtr + 1]['content'], '-')
            && in_array($tokens[$stackPtr - 1]['content'], $this->symbolsBeforeConcat)
        ) {
            $phpcsFile->addError('Concatenation is used', $stackPtr);
        }
    }
}
