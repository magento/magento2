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
    const S_ASPERAND            = '&';
    const S_COLON               = ';';
    const S_OPEN_PARENTHESIS    = '(';
    const S_CLOSE_PARENTHESIS   = ')';
    const S_NEW_LINE            = "\n";
    const S_WHITESPACE          = ' ';
    const S_DOUBLE_WHITESPACE   = '  ';
    const S_INDENT_SPACES       = '    ';
    /**#@-*/
}
