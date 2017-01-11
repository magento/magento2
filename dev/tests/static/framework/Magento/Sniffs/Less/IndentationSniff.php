<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class IndentationSniff
 *
 * Ensures styles are indented 4 spaces.
 *
 * @see Squiz_Sniffs_CSS_IndentationSniff
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#indentation
 */
class IndentationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * A nesting level than this value will throw a warning.
     *
     * @var int
     */
    public $maxIndentLevel = 3;

    /** Skip codes that can be detected by sniffer incorrectly
     *
     * @var array
     */
    private $styleCodesToSkip = [T_ASPERAND, T_COLON, T_OPEN_PARENTHESIS, T_CLOSE_PARENTHESIS];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $numTokens = (count($tokens) - 2);
        $indentLevel = 0;
        for ($i = 1; $i < $numTokens; $i++) {
            if ($tokens[$i]['code'] === T_COMMENT) {
                // Don't check the indent of comments.
                continue;
            }

            if ($tokens[$i]['code'] === T_OPEN_CURLY_BRACKET) {
                $indentLevel++;
            } elseif ($tokens[($i + 1)]['code'] === T_CLOSE_CURLY_BRACKET) {
                $indentLevel--;
            }

            if ($tokens[$i]['column'] !== 1) {
                continue;
            }

            // We started a new line, so check indent.
            if ($tokens[$i]['code'] === T_WHITESPACE) {
                $content = str_replace($phpcsFile->eolChar, '', $tokens[$i]['content']);
                $foundIndent = strlen($content);
            } else {
                $foundIndent = 0;
            }

            $expectedIndent = ($indentLevel * $this->indent);
            if (!($expectedIndent > 0 && strpos($tokens[$i]['content'], $phpcsFile->eolChar) !== false)
                && ($foundIndent !== $expectedIndent)
                && (!in_array($tokens[$i + 1]['code'], $this->styleCodesToSkip))
            ) {
                $error = 'Line indented incorrectly; expected %s spaces, found %s';
                $phpcsFile->addError($error, $i, 'Incorrect', [$expectedIndent, $foundIndent]);
            }

            if ($indentLevel > $this->maxIndentLevel) {
                // Will be implemented in MAGETWO-49778
                // $phpcsFile->addWarning('Avoid using more than three levels of nesting', $i, 'IncorrectNestingLevel');
            }
        }
    }
}
