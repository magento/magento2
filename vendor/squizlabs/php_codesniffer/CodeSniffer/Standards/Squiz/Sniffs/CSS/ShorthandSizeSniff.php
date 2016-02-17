<?php
/**
 * Squiz_Sniffs_CSS_ShorthandSizeSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Squiz_Sniffs_CSS_ShorthandSizeSniff.
 *
 * Ensure sizes are defined using shorthand notation where possible, except in the
 * case where shorthand becomes 3 values.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Squiz_Sniffs_CSS_ShorthandSizeSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('CSS');

    /**
     * A list of styles that we shouldn't check.
     *
     * These have values that looks like sizes, but are not.
     *
     * @var array
     */
    public $excludeStyles = array(
                             'background-position',
                             'box-shadow',
                             'transform-origin',
                             '-webkit-transform-origin',
                             '-ms-transform-origin',
                            );


    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_STYLE);

    }//end register()


    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Some styles look like shorthand but are not actually a set of 4 sizes.
        $style = strtolower($tokens[$stackPtr]['content']);
        if (in_array($style, $this->excludeStyles) === true) {
            return;
        }

        // Get the whole style content.
        $end     = $phpcsFile->findNext(T_SEMICOLON, ($stackPtr + 1));
        $content = $phpcsFile->getTokensAsString(($stackPtr + 1), ($end - $stackPtr - 1));
        $content = trim($content, ': ');

        // Account for a !important annotation.
        if (substr($content, -10) === '!important') {
            $content = substr($content, 0, -10);
            $content = trim($content);
        }

        // Check if this style value is a set of numbers with optional prefixes.
        $content = preg_replace('/\s+/', ' ', $content);
        $values  = array();
        $num     = preg_match_all(
            '/([0-9]+)([a-zA-Z]{2}\s+|%\s+|\s+)/',
            $content.' ',
            $values,
            PREG_SET_ORDER
        );

        // Only interested in styles that have multiple sizes defined.
        if ($num < 2) {
            return;
        }

        // Rebuild the content we matched to ensure we got everything.
        $matched = '';
        foreach ($values as $value) {
            $matched .= $value[0];
        }

        if ($content !== trim($matched)) {
            return;
        }

        if ($num === 3) {
            $expected = trim($content.' '.$values[1][1].$values[1][2]);
            $error = 'Shorthand syntax not allowed here; use %s instead';
            $data  = array($expected);
            $phpcsFile->addError($error, $stackPtr, 'NotAllowed', $data);
            return;
        }

        if ($num === 2) {
            if ($values[0][0] !== $values[1][0]) {
                // Both values are different, so it is already shorthand.
                return;
            }
        } else if ($values[0][0] !== $values[2][0] || $values[1][0] !== $values[3][0]) {
            // Can't shorthand this.
            return;
        }

        if ($values[0][0] === $values[1][0]) {
            // All values are the same.
            $expected = $values[0][0];
        } else {
            $expected = $values[0][0].' '.$values[1][0];
        }

        $expected = preg_replace('/\s+/', ' ', trim($expected));

        $error = 'Size definitions must use shorthand if available; expected "%s" but found "%s"';
        $data  = array(
                  $expected,
                  $content,
                 );

        $phpcsFile->addError($error, $stackPtr, 'NotUsed', $data);

    }//end process()


}//end class
?>
