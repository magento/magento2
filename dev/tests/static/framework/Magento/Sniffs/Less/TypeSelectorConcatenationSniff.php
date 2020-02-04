<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class TypeSelectorConcatenation
 *
 * Ensure that selector in one line, concatenation is not used
 *
 * @link https://devdocs.magento.com/guides/v2.3/coding-standards/code-standard-less.html#formatting-1
 */
class TypeSelectorConcatenationSniff implements Sniff
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
     * @inheritdoc
     */
    public function register()
    {
        return [T_BITWISE_AND];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (0 === strpos($tokens[$stackPtr + 1]['content'], '-')
            && in_array($tokens[$stackPtr - 1]['content'], $this->symbolsBeforeConcat)
        ) {
            $phpcsFile->addError('Concatenation is used', $stackPtr, 'ConcatenationUsage');
        }
    }
}
