<?php
/**
 * Generic_Sniffs_NamingConventions_UpperCaseConstantNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Generic_Sniffs_NamingConventions_UpperCaseConstantNameSniff.
 *
 * Ensures that constant names are all uppercase.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Generic_Sniffs_NamingConventions_UpperCaseConstantNameSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens    = $phpcsFile->getTokens();
        $constName = $tokens[$stackPtr]['content'];

        // If this token is in a heredoc, ignore it.
        if ($phpcsFile->hasCondition($stackPtr, T_START_HEREDOC) === true) {
            return;
        }

        // Special case for PHP 5.5 class name resolution.
        if (strtolower($constName) === 'class'
            && $tokens[($stackPtr - 1)]['code'] === T_DOUBLE_COLON
        ) {
            return;
        }

        // Special case for PHPUnit.
        if ($constName === 'PHPUnit_MAIN_METHOD') {
            return;
        }

        // If the next non-whitespace token after this token
        // is not an opening parenthesis then it is not a function call.
        $openBracket = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            $functionKeyword = $phpcsFile->findPrevious(
                array(
                 T_WHITESPACE,
                 T_COMMA,
                 T_COMMENT,
                 T_STRING,
                 T_NS_SEPARATOR,
                ),
                ($stackPtr - 1),
                null,
                true
            );

            if ($tokens[$functionKeyword]['code'] !== T_CONST) {
                return;
            }

            // This is a class constant.
            if (strtoupper($constName) !== $constName) {
                $error = 'Class constants must be uppercase; expected %s but found %s';
                $data  = array(
                          strtoupper($constName),
                          $constName,
                         );
                $phpcsFile->addError($error, $stackPtr, 'ClassConstantNotUpperCase', $data);
            }

            return;
        }

        if (strtolower($constName) !== 'define') {
            return;
        }

        /*
            This may be a "define" function call.
        */

        // Make sure this is not a method call.
        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($tokens[$prev]['code'] === T_OBJECT_OPERATOR
            || $tokens[$prev]['code'] === T_DOUBLE_COLON
        ) {
            return;
        }

        // The next non-whitespace token must be the constant name.
        $constPtr = $phpcsFile->findNext(T_WHITESPACE, ($openBracket + 1), null, true);
        if ($tokens[$constPtr]['code'] !== T_CONSTANT_ENCAPSED_STRING) {
            return;
        }

        $constName = $tokens[$constPtr]['content'];

        // Check for constants like self::CONSTANT.
        $prefix   = '';
        $splitPos = strpos($constName, '::');
        if ($splitPos !== false) {
            $prefix    = substr($constName, 0, ($splitPos + 2));
            $constName = substr($constName, ($splitPos + 2));
        }

        if (strtoupper($constName) !== $constName) {
            $error = 'Constants must be uppercase; expected %s but found %s';
            $data  = array(
                      $prefix.strtoupper($constName),
                      $prefix.$constName,
                     );
            $phpcsFile->addError($error, $stackPtr, 'ConstantNotUpperCase', $data);
        }

    }//end process()


}//end class

?>
