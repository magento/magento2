<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class ColourDefinitionSniff
 *
 * Ensure that hexadecimal values are used for variables not for properties
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#hexadecimal-notation
 *
 */
class ColourDefinitionSniff implements PHP_CodeSniffer_Sniff
{
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
        return [T_COLOUR];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $colour = $tokens[$stackPtr]['content'];

        $variablePtr = $phpcsFile->findPrevious(T_ASPERAND, $stackPtr);
        if ((false === $variablePtr) || ($tokens[$stackPtr]['line'] !== $tokens[$variablePtr]['line'])) {
            $phpcsFile->addError('Hexadecimal value should be used for variable', $stackPtr, 'NotInVariable');
        }

        $expected = strtolower($colour);
        if ($colour !== $expected) {
            $error = 'CSS colours must be defined in lowercase; expected %s but found %s';
            $phpcsFile->addError($error, $stackPtr, 'NotLower', [$expected, $colour]);
        }

        // Now check if shorthand can be used.
        if (strlen($colour) !== 7) {
            return;
        }

        if ($colour[1] === $colour[2] && $colour[3] === $colour[4] && $colour[5] === $colour[6]) {
            $expected = '#' . $colour[1] . $colour[3] . $colour[5];
            $error = 'CSS colours must use shorthand if available; expected %s but found %s';
            $phpcsFile->addError($error, $stackPtr, 'Shorthand', [$expected, $colour]);
        }
    }
}
