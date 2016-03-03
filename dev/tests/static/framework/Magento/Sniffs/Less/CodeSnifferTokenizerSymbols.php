<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

/**
 * Class CodeSnifferTokenizerSymbols
 *
 */
class CodeSnifferTokenizerSymbols
{
    const TOKENIZER_CSS = 'CSS';

    /**#@+
     * Symbols for usage into Sniffers
     */
    const STRING_ASPERAND            = '&';
    const STRING_COLON               = ';';
    const STRING_OPEN_PARENTHESIS    = '(';
    const STRING_CLOSE_PARENTHESIS   = ')';
    const STRING_NEW_LINE            = "\n";
    const STRING_WHITESPACE          = ' ';
    const STRING_DOUBLE_WHITESPACE   = '  ';
    const STRING_INDENT_SPACES       = '    ';
    /**#@-*/
}
