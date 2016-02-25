<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class SemicolonSpacingSniff
 *
 * Property should have a semicolon at the end of line
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#end-of-the-property-line
 *
 */
class SemicolonSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = ['CSS'];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_STYLE];
    }

    /**
     * Skip symbols that can be detected by sniffer incorrectly
     *
     * @var array
     */
    private $styleSymbolsToSkip = ['&', ';', '(', ')'];

    /** Skip codes that can be detected by sniffer incorrectly
     *
     * @var array
     */
    private $styleCodesToSkip = [T_ASPERAND, T_COLON, T_OPEN_PARENTHESIS, T_CLOSE_PARENTHESIS];

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (in_array($tokens[$stackPtr]['content'], $this->styleSymbolsToSkip)) {
            return;
        }

        $semicolon = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
        if ($tokens[$semicolon]['line'] !== $tokens[$stackPtr]['line']) {
            $semicolon = $phpcsFile->findNext(T_STYLE, ($stackPtr + 1), null, false, ";");
        }

        if ((false === $semicolon || $tokens[$semicolon]['line'] !== $tokens[$stackPtr]['line'])
            && (isset($tokens[$stackPtr - 1]) && !in_array($tokens[$stackPtr - 1]['code'], $this->styleCodesToSkip))
            && (T_COLON !== $tokens[$stackPtr + 1]['code'])
        ) {
            $error = 'Style definitions must end with a semicolon';
            $phpcsFile->addError($error, $stackPtr, 'NotAtEnd');
            return;
        }

        if (!isset($tokens[($semicolon - 1)])) {
            return;
        }

        if ($tokens[($semicolon - 1)]['code'] === T_WHITESPACE) {
            $length  = strlen($tokens[($semicolon - 1)]['content']);
            $error = 'Expected 0 spaces before semicolon in style definition; %s found';
            $data  = [$length];
            $phpcsFile->addError($error, $stackPtr, 'SpaceFound', $data);
        }
    }
}
