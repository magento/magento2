<?php
/**
 * PEAR_Sniffs_Functions_FunctionCallSignatureSniff.
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
 * PEAR_Sniffs_Functions_FunctionCallSignatureSniff.
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
class PEAR_Sniffs_Functions_FunctionCallSignatureSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * If TRUE, multiple arguments can be defined per line in a multi-line call.
     *
     * @var bool
     */
    public $allowMultipleArguments = true;

    /**
     * How many spaces should follow the opening bracket.
     *
     * @var int
     */
    public $requiredSpacesAfterOpen = 0;

    /**
     * How many spaces should precede the closing bracket.
     *
     * @var int
     */
    public $requiredSpacesBeforeClose = 0;


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
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->requiredSpacesAfterOpen   = (int) $this->requiredSpacesAfterOpen;
        $this->requiredSpacesBeforeClose = (int) $this->requiredSpacesBeforeClose;
        $tokens = $phpcsFile->getTokens();

        // Find the next non-empty token.
        $openBracket = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a function call.
            return;
        }

        if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
            // Not a function call.
            return;
        }

        // Find the previous non-empty token.
        $search   = PHP_CodeSniffer_Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return;
        }

        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

        if (($stackPtr + 1) !== $openBracket) {
            // Checking this: $value = my_function[*](...).
            $error = 'Space before opening parenthesis of function call prohibited';
            $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeOpenBracket');
        }

        $next = $phpcsFile->findNext(T_WHITESPACE, ($closeBracket + 1), null, true);
        if ($tokens[$next]['code'] === T_SEMICOLON) {
            if (in_array($tokens[($closeBracket + 1)]['code'], PHP_CodeSniffer_Tokens::$emptyTokens) === true) {
                $error = 'Space after closing parenthesis of function call prohibited';
                $phpcsFile->addError($error, $closeBracket, 'SpaceAfterCloseBracket');
            }
        }

        // Check if this is a single line or multi-line function call.
        if ($this->isMultiLineCall($phpcsFile, $stackPtr, $openBracket, $tokens) === true) {
            $this->processMultiLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
        } else {
            $this->processSingleLineCall($phpcsFile, $stackPtr, $openBracket, $tokens);
        }

    }//end process()


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
        if ($tokens[$openBracket]['line'] !== $tokens[$closeBracket]['line']) {
            return true;
        }

        return false;

    }//end isMultiLineCall()


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
    public function processSingleLineCall(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {

        $closer = $tokens[$openBracket]['parenthesis_closer'];
        if ($openBracket === ($closer - 1)) {
            return;
        }

        if ($this->requiredSpacesAfterOpen === 0 && $tokens[($openBracket + 1)]['code'] === T_WHITESPACE) {
            // Checking this: $value = my_function([*]...).
            $error = 'Space after opening parenthesis of function call prohibited';
            $phpcsFile->addError($error, $stackPtr, 'SpaceAfterOpenBracket');
        } else if ($this->requiredSpacesAfterOpen > 0) {
            $spaceAfterOpen = 0;
            if ($tokens[($openBracket + 1)]['code'] === T_WHITESPACE) {
                $spaceAfterOpen = strlen($tokens[($openBracket + 1)]['content']);
            }

            if ($spaceAfterOpen !== $this->requiredSpacesAfterOpen) {
                $error = 'Expected %s spaces after opening bracket; %s found';
                $data  = array(
                          $this->requiredSpacesAfterOpen,
                          $spaceAfterOpen,
                         );
                $phpcsFile->addError($error, $stackPtr, 'SpaceAfterOpenBracket', $data);
            }
        }

        // Checking this: $value = my_function(...[*]).
        $spaceBeforeClose = 0;
        if ($tokens[($closer - 1)]['code'] === T_WHITESPACE) {
            $spaceBeforeClose = strlen($tokens[($closer - 1)]['content']);
        }

        if ($spaceBeforeClose !== $this->requiredSpacesBeforeClose) {
            $error = 'Expected %s spaces before closing bracket; %s found';
            $data  = array(
                      $this->requiredSpacesBeforeClose,
                      $spaceBeforeClose,
                     );
            $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeCloseBracket', $data);
        }

    }//end processSingleLineCall()


    /**
     * Processes multi-line calls.
     *
     * @param PHP_CodeSniffer_File $phpcsFile   The file being scanned.
     * @param int                  $stackPtr    The position of the current token
     *                                          in the stack passed in $tokens.
     * @param int                  $openBracket The position of the openning bracket
     *                                          in the stack passed in $tokens.
     * @param array                $tokens      The stack of tokens that make up
     *                                          the file.
     *
     * @return void
     */
    public function processMultiLineCall(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        // We need to work out how far indented the function
        // call itself is, so we can work out how far to
        // indent the arguments.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($i > 0 && $tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // Each line between the parenthesis should be indented n spaces.
        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];
        $lastLine     = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            // Skip nested function calls.
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i        = $tokens[$i]['parenthesis_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }

            if ($tokens[$i]['line'] !== $lastLine) {
                $lastLine = $tokens[$i]['line'];

                // Ignore heredoc indentation.
                if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$heredocTokens) === true) {
                    continue;
                }

                // Ignore multi-line string indentation.
                if (in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$stringTokens) === true) {
                    if ($tokens[$i]['code'] === $tokens[($i - 1)]['code']) {
                        continue;
                    }
                }

                // We changed lines, so this should be a whitespace indent token, but first make
                // sure it isn't a blank line because we don't need to check indent unless there
                // is actually some code to indent.
                if ($tokens[$i]['code'] === T_WHITESPACE) {
                    $nextCode = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), ($closeBracket + 1), true);
                    if ($tokens[$nextCode]['line'] !== $lastLine) {
                        $error = 'Empty lines are not allowed in multi-line function calls';
                        $phpcsFile->addError($error, $i, 'EmptyLine');
                        continue;
                    }
                } else {
                    $nextCode = $i;
                }

                // Check if the next line contains an object operator, if so rely on
                // the ObjectOperatorIndentSniff to test the indent.
                if ($tokens[$nextCode]['type'] === 'T_OBJECT_OPERATOR') {
                    continue;
                }

                if ($nextCode === $closeBracket) {
                    // Closing brace needs to be indented to the same level
                    // as the function call.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + $this->indent);
                }

                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    // Just check if it is a multi-line block comment. If so, we can
                    // calculate the indent from the whitespace before the content.
                    if ($tokens[$i]['code'] === T_COMMENT
                        && $tokens[($i - 1)]['code'] === T_COMMENT
                    ) {
                        $trimmed     = ltrim($tokens[$i]['content']);
                        $foundIndent = (strlen($tokens[$i]['content']) - strlen($trimmed));
                    } else {
                        $foundIndent = 0;
                    }
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function call not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                              $expectedIndent,
                              $foundIndent,
                             );
                    $phpcsFile->addError($error, $i, 'Indent', $data);
                }
            }//end if

            // Skip the rest of a closure.
            if ($tokens[$i]['code'] === T_CLOSURE) {
                $i        = $tokens[$i]['scope_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }

            // Skip the rest of a short array.
            if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                $i        = $tokens[$i]['bracket_closer'];
                $lastLine = $tokens[$i]['line'];
                continue;
            }

            if ($this->allowMultipleArguments === false && $tokens[$i]['code'] === T_COMMA) {
                // Comma has to be the last token on the line.
                $next = $phpcsFile->findNext(array(T_WHITESPACE, T_COMMENT), ($i + 1), $closeBracket, true);
                if ($next !== false
                    && $tokens[$i]['line'] === $tokens[$next]['line']
                ) {
                    $error = 'Only one argument is allowed per line in a multi-line function call';
                    $phpcsFile->addError($error, $next, 'MultipleArguments');
                }
            }
        }//end for

        if ($tokens[($openBracket + 1)]['content'] !== $phpcsFile->eolChar) {
            $error = 'Opening parenthesis of a multi-line function call must be the last content on the line';
            $phpcsFile->addError($error, $stackPtr, 'ContentAfterOpenBracket');
        }

        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBracket - 1), null, true);
        if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
            $error = 'Closing parenthesis of a multi-line function call must be on a line by itself';
            $phpcsFile->addError($error, $closeBracket, 'CloseBracketLine');
        }

    }//end processMultiLineCall()


}//end class
