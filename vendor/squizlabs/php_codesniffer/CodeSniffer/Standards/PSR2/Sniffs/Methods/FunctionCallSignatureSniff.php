<?php
/**
 * PSR2_Sniffs_Methods_FunctionCallSignatureSniff.
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
 * PSR2_Sniffs_Methods_FunctionCallSignatureSniff.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PSR2_Sniffs_Methods_FunctionCallSignatureSniff extends PEAR_Sniffs_Functions_FunctionCallSignatureSniff
{

    /**
     * If TRUE, multiple arguments can be defined per line in a multi-line call.
     *
     * @var bool
     */
    public $allowMultipleArguments = false;


    /**
     * Processes single-line calls.
     *
     * @param PHP_CodeSniffer_File $phpcsFile   The file being scanned.
     * @param int                  $stackPtr    The position of the current token
     *                                          in the stack passed in $tokens.
     * @param int                  $openBracket The position of the opening bracket
     *                                          in the stack passed in $tokens.
     * @param array                $tokens      The stack of tokens that make up
     *                                          the file.
     *
     * @return void
     */
    public function isMultiLineCall(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];
        $compareLine  = $tokens[$openBracket]['line'];

        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'];
                $compareLine = $tokens[$i]['line'];
                continue;
            } else if ($tokens[$i]['code'] === T_CLOSURE) {
                $i = $tokens[$i]['scope_closer'];
                $compareLine = $tokens[$i]['line'];
                continue;
            } else if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                $i = $tokens[$i]['bracket_closer'];
                $compareLine = $tokens[$i]['line'];
                continue;
            }

            if ($tokens[$i]['line'] !== $compareLine) {
                return true;
            }
        }//end for

        return false;

    }//end isMultiLineCall()


}//end class
?>
