<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * Class ZeroUnitsSniff
 *
 * Ensure that units for 0 is not specified
 * Omit leading "0"s in values, use dot instead
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#and-units
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#floating-values
 *
 */
class ZeroUnitsSniff implements PHP_CodeSniffer_Sniff
{
    const CSS_PROPERTY_UNIT_PX = 'px';
    const CSS_PROPERTY_UNIT_EM = 'em';
    const CSS_PROPERTY_UNIT_REM = 'rem';

    /**
     * List of available CSS Propery units
     *
     * @var array
     */
    private $units = [
        self::CSS_PROPERTY_UNIT_PX,
        self::CSS_PROPERTY_UNIT_EM,
        self::CSS_PROPERTY_UNIT_REM,
    ];

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
        return [T_LNUMBER, T_DNUMBER];
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenCode = $tokens[$stackPtr]['code'];
        $tokenContent = $tokens[$stackPtr]['content'];

        $nextToken = $tokens[$stackPtr + 1];

        if (T_LNUMBER === $tokenCode
            && "0" === $tokenContent
            && T_STRING === $nextToken['code']
            && in_array($nextToken['content'], $this->units)
        ) {
            $phpcsFile->addError('Units specified for "0" value', $stackPtr);
        }

        if ((T_DNUMBER === $tokenCode)
            && 0 === strpos($tokenContent, "0")
            && ((float)$tokenContent < 1)
        ) {
            $phpcsFile->addError('Values starts from "0"', $stackPtr);
        }
    }
}
