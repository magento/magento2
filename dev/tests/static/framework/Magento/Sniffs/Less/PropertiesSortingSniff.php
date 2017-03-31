<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class PropertiesSortingSniff
 *
 * Ensure that properties are sorted alphabetically
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#sorting
 *
 */
class PropertiesSortingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * List of properties that belong to class
     *
     * @var array
     */
    private $properties = [];

    /**
     * Skip symbols that can be detected by sniffer incorrectly
     *
     * @var array
     */
    private $styleSymbolsToSkip = [
        TokenizerSymbolsInterface::BITWISE_AND,
        TokenizerSymbolsInterface::COLON,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_OPEN_CURLY_BRACKET,
            T_CLOSE_CURLY_BRACKET,
            T_OPEN_PARENTHESIS,
            T_CLOSE_PARENTHESIS,
            T_STYLE
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $currentToken = $tokens[$stackPtr];

        // if variables, mixins, extends area used - skip
        if ((T_ASPERAND === $tokens[$stackPtr - 1]['code'])
            || in_array($tokens[$stackPtr]['content'], $this->styleSymbolsToSkip)
        ) {
            return;
        }

        $nextCurlyBracket = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $stackPtr + 1);
        if (in_array($currentToken['code'], [T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET])
            || ((false !== $nextCurlyBracket) && ($tokens[$nextCurlyBracket]['line'] === $tokens[$stackPtr]['line']))
        ) {
            if ($this->properties) {
                // validate collected properties before erase them
                $this->validatePropertiesSorting($phpcsFile, $stackPtr, $this->properties);
            }

            $this->properties = [];
            return;
        }

        if (T_STYLE === $currentToken['code']) {
            $this->properties[] = $currentToken['content'];
        }
    }

    /**
     * Validate sorting of properties of class
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @param array $properties
     *
     * @return void
     */
    private function validatePropertiesSorting(PHP_CodeSniffer_File $phpcsFile, $stackPtr, array $properties)
    {

        $originalProperties = $properties;
        sort($properties);

        if ($originalProperties !== $properties) {
            $delimiter = $phpcsFile->findPrevious(T_SEMICOLON, $stackPtr);
            $phpcsFile->addError('Properties sorted not alphabetically', $delimiter);
        }
    }
}
