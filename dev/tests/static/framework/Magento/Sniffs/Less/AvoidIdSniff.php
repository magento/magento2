<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class AvoidIdSniff
 *
 * Ensure that id selector is not used
 *
 * @link https://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#types
 */
class AvoidIdSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * Tokens that can appear in a selector
     *
     * @var array
     */
    private $selectorTokens = [
        T_HASH,
        T_WHITESPACE,
        T_STRING_CONCAT,
        T_OPEN_PARENTHESIS,
        T_CLOSE_PARENTHESIS,
        T_OPEN_SQUARE_BRACKET,
        T_CLOSE_SQUARE_BRACKET,
        T_DOUBLE_QUOTED_STRING,
        T_CONSTANT_ENCAPSED_STRING,
        T_DOUBLE_COLON,
        T_COLON,
        T_EQUAL,
        T_MUL_EQUAL,
        T_OR_EQUAL,
        T_STRING,
        T_NONE,
        T_DOLLAR,
        T_GREATER_THAN,
        T_PLUS,
        T_NS_SEPARATOR,
        T_LNUMBER,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_HASH];
    }

    /**
     * @inheritdoc
     *
     * Will flag any selector that looks like the following:
     * #foo[bar],
     * #foo[bar=bash],
     * #foo[bar~=bash],
     * #foo[bar$=bash],
     * #foo[bar*=bash],
     * #foo[bar|=bash],
     * #foo[bar='bash'],
     * #foo:hover,
     * #foo:nth-last-of-type(n),
     * #foo::before,
     * #foo + div,
     * #foo > div,
     * #foo ~ div,
     * #foo\3Abar ~ div,
     * #foo\:bar ~ div,
     * #foo.bar .baz,
     * div#foo {
     *     blah: 'abc';
     * }
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the next non-selector token
        $nextToken = $phpcsFile->findNext($this->selectorTokens, $stackPtr + 1, null, true);

        // Anything except a { or a , means this is not a selector
        if ($nextToken !== false && in_array($tokens[$nextToken]['code'], [T_OPEN_CURLY_BRACKET, T_COMMA])) {
            $phpcsFile->addError('Id selector is used', $stackPtr, 'IdSelectorUsage');
        }
    }
}
