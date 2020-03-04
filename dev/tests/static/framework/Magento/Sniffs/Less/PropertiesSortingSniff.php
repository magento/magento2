<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class PropertiesSortingSniff
 *
 * Ensure that properties are sorted alphabetically
 *
 * @link https://devdocs.magento.com/guides/v2.3/coding-standards/code-standard-less.html#sorting
 */
class PropertiesSortingSniff implements Sniff
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
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
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $properties
     *
     * @return void
     */
    private function validatePropertiesSorting(File $phpcsFile, $stackPtr, array $properties)
    {
        // Fix needed for cases when incorrect properties passed for validation due to bug in PHP tokens.
        $symbolsForSkip = ['(', 'block', 'field'];
        $properties = array_filter(
            $properties,
            function ($var) use ($symbolsForSkip) {
                return !in_array($var, $symbolsForSkip);
            }
        );

        $originalProperties = $properties;
        sort($properties);

        if ($originalProperties !== $properties) {
            $delimiter = $phpcsFile->findPrevious(T_SEMICOLON, $stackPtr);
            $phpcsFile->addError('Properties sorted not alphabetically', $delimiter, 'PropertySorting');
        }
    }
}
