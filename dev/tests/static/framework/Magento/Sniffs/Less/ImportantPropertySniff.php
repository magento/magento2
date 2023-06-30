<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class ImportantPropertySniff
 *
 * Ensure that single quotes are used
 *
 * @link https://devdocs.magento.com/guides/v2.4/coding-standards/code-standard-less.html#important-property
 */
class ImportantPropertySniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_BOOLEAN_NOT];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
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
