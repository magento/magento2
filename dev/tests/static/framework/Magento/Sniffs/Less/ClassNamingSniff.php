<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class ClassNamingSniff
 *
 * Ensure that class name responds to the following requirements:
 *
 * - names should be lowercase;
 * - start with a letter (except helper classes);
 * - words should be separated with dash '-';
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#standard-classes
 *
 */
class ClassNamingSniff implements PHP_CodeSniffer_Sniff
{

    const STRING_HELPER_CLASSES_PREFIX = '_';

    const STRING_ALLOWED_UNDERSCORES = '__';

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
        return [T_STRING_CONCAT];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (T_WHITESPACE !== $tokens[$stackPtr - 1]['code']
            && !in_array($tokens[$stackPtr - 1]['content'], [
                TokenizerSymbolsInterface::INDENT_SPACES,
                TokenizerSymbolsInterface::NEW_LINE,
            ])
        ) {
            return;
        }

        $className = $tokens[$stackPtr + 1]['content'];
        if (preg_match_all('/[^a-z0-9\-_]/U', $className, $matches)) {
            $phpcsFile->addError('Class name contains not allowed symbols', $stackPtr, 'NotAllowedSymbol', $matches);
        }

        if (!empty(strpos($className, self::STRING_HELPER_CLASSES_PREFIX))
            && empty(strpos($className, self::STRING_ALLOWED_UNDERSCORES))
        ) {
            $phpcsFile->addError('"_" symbol allowed only for helper classes', $stackPtr, 'UnderscoreSymbol');
        }
    }
}
