<?php
namespace Magento\Sniffs\Annotations;

use PHP_CodeSniffer_CommentParser_ClassCommentParser;
use PHP_CodeSniffer_CommentParser_CommentElement;
use PHP_CodeSniffer_CommentParser_MemberCommentParser;
use PHP_CodeSniffer_CommentParser_ParserException;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Standards_AbstractVariableSniff;

include_once 'Helper.php';
/**
 * Parses and verifies the variable doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A variable doc comment exists.</li>
 *  <li>Short description ends with a full stop.</li>
 *  <li>There is a blank line after the short description.</li>
 *  <li>There is a blank line between the description and the tags.</li>
 *  <li>Check the order, indentation and content of each tag.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 *
 * @SuppressWarnings(PHPMD)
 */
class RequireAnnotatedAttributesSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    /**
     * The header comment parser for the current file.
     *
     * @var PHP_CodeSniffer_CommentParser_ClassCommentParser
     */
    protected $commentParser = null;

    /**
     * The sniff helper for stuff shared between the annotations sniffs
     *
     * @var Helper
     */
    protected $helper = null;

    /**
     * Extract the var comment docblock
     *
     * @param array $tokens
     * @param string $commentToken
     * @param int $stackPtr  The position of the current token in the stack passed in $tokens.
     * @return int|false
     */
    protected function extractVarDocBlock($tokens, $commentToken, $stackPtr)
    {
        $commentEnd = $this->helper->getCurrentFile()->findPrevious($commentToken, $stackPtr - 3);
        $break = false;
        if ($commentEnd !== false && $tokens[$commentEnd]['code'] === T_COMMENT) {
            $this->helper->addMessage($stackPtr, Helper::WRONG_STYLE, ['variable']);
            $break = true;
        } elseif ($commentEnd === false || $tokens[$commentEnd]['code'] !== T_DOC_COMMENT) {
            $this->helper->addMessage($stackPtr, Helper::MISSING, ['variable']);
            $break = true;
        } else {
            // Make sure the comment we have found belongs to us.
            $commentFor = $this->helper->getCurrentFile()->findNext(
                [T_VARIABLE, T_CLASS, T_INTERFACE],
                $commentEnd + 1
            );
            if ($commentFor !== $stackPtr) {
                $this->helper->addMessage($stackPtr, Helper::MISSING, ['variable']);
                $break = true;
            }
        }
        return $break ? false : $commentEnd;
    }

    /**
     * Checks for short and long descriptions on variable definitions
     *
     * @param PHP_CodeSniffer_CommentParser_CommentElement $comment
     * @param int $commentStart
     * @return void
     */
    protected function checkForDescription($comment, $commentStart)
    {
        $short = $comment->getShortComment();
        $long = '';
        $newlineCount = 0;
        if (trim($short) === '') {
            $this->helper->addMessage($commentStart, Helper::MISSING_SHORT, ['variable']);
            $newlineCount = 1;
        } else {
            // No extra newline before short description.
            $newlineSpan = strspn($short, $this->helper->getEolChar());
            if ($short !== '' && $newlineSpan > 0) {
                $this->helper->addMessage($commentStart + 1, Helper::SPACING_BEFORE_SHORT, ['variable']);
            }

            $newlineCount = substr_count($short, $this->helper->getEolChar()) + 1;

            // Exactly one blank line between short and long description.
            $long = $comment->getLongComment();
            if (empty($long) === false) {
                $between = $comment->getWhiteSpaceBetween();
                $newlineBetween = substr_count($between, $this->helper->getEolChar());
                if ($newlineBetween !== 2) {
                    $this->helper->addMessage(
                        $commentStart + $newlineCount + 1,
                        Helper::SPACING_BETWEEN,
                        ['variable']
                    );
                }

                $newlineCount += $newlineBetween;

                $testLong = trim($long);
                if (preg_match('|\p{Lu}|u', $testLong[0]) === 0) {
                    $this->helper->addMessage(
                        $commentStart + $newlineCount,
                        Helper::LONG_NOT_CAPITAL,
                        ['Variable']
                    );
                }
            }

            // Short description must be single line and end with a full stop.
            $testShort = trim($short);
            $lastChar = $testShort[strlen($testShort) - 1];
            if (substr_count($testShort, $this->helper->getEolChar()) !== 0) {
                $this->helper->addMessage($commentStart + 1, Helper::SHORT_SINGLE_LINE, ['Variable']);
            }

            if (preg_match('|\p{Lu}|u', $testShort[0]) === 0) {
                $this->helper->addMessage($commentStart + 1, Helper::SHORT_NOT_CAPITAL, ['Variable']);
            }

            if ($lastChar !== '.') {
                $this->helper->addMessage($commentStart + 1, Helper::SHORT_FULL_STOP, ['Variable']);
            }
        }
        // Exactly one blank line before tags.
        $tags = $this->commentParser->getTagOrders();
        if (count($tags) > 1) {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                if ($long !== '') {
                    $newlineCount += substr_count($long, $this->helper->getEolChar()) - $newlineSpan + 1;
                }

                $this->helper->addMessage(
                    $commentStart + $newlineCount,
                    Helper::SPACING_BEFORE_TAGS,
                    ['variable']
                );
                $short = rtrim($short, $this->helper->getEolChar() . ' ');
            }
        }
    }

    /**
     * Called to process class member vars.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->helper = new Helper($phpcsFile);
        // if we should skip this type we should do that
        if ($this->helper->shouldFilter()) {
            return;
        }
        $tokens = $phpcsFile->getTokens();
        $commentToken = [T_COMMENT, T_DOC_COMMENT];

        // Extract the var comment docblock.
        $commentEnd = $this->extractVarDocBlock($tokens, $commentToken, $stackPtr);
        if ($commentEnd === false) {
            return;
        }

        $commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT, $commentEnd - 1, null, true) + 1;
        $commentString = $phpcsFile->getTokensAsString($commentStart, $commentEnd - $commentStart + 1);

        // Parse the header comment docblock.
        try {
            $this->commentParser = new PHP_CodeSniffer_CommentParser_MemberCommentParser($commentString, $phpcsFile);
            $this->commentParser->parse();
        } catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = $e->getLineWithinComment() + $commentStart;
            $data = [$e->getMessage()];
            $this->helper->addMessage($line, Helper::ERROR_PARSING, $data);
            return;
        }

        $comment = $this->commentParser->getComment();
        if (($comment === null) === true) {
            $this->helper->addMessage($commentStart, Helper::EMPTY_DOC, ['Variable']);
            return;
        }

        // The first line of the comment should just be the /** code.
        $eolPos = strpos($commentString, $phpcsFile->eolChar);
        $firstLine = substr($commentString, 0, $eolPos);
        if ($firstLine !== '/**') {
            $this->helper->addMessage($commentStart, Helper::CONTENT_AFTER_OPEN);
        }

        // Check for a comment description.
        $this->checkForDescription($comment, $commentStart);

        // Check for unknown/deprecated tags.
        $unknownTags = $this->commentParser->getUnknown();
        foreach ($unknownTags as $errorTag) {
            // Unknown tags are not parsed, do not process further.
            $data = [$errorTag['tag']];
            $this->helper->addMessage($commentStart + $errorTag['line'], Helper::TAG_NOT_ALLOWED, $data);
        }

        // Check each tag.
        $this->processVar($commentStart, $commentEnd);
        $this->processSees($commentStart);

        // The last content should be a newline and the content before
        // that should not be blank. If there is more blank space
        // then they have additional blank lines at the end of the comment.
        $words = $this->commentParser->getWords();
        $lastPos = count($words) - 1;
        if (trim(
            $words[$lastPos - 1]
        ) !== '' || strpos(
            $words[$lastPos - 1],
            $this->currentFile->eolChar
        ) === false || trim(
            $words[$lastPos - 2]
        ) === ''
        ) {
            $this->helper->addMessage($commentEnd, Helper::SPACING_AFTER, ['variable']);
        }
    }

    /**
     * Process the var tag.
     *
     * @param int $commentStart The position in the stack where the comment started.
     * @param int $commentEnd   The position in the stack where the comment ended.
     *
     * @return void
     */
    protected function processVar($commentStart, $commentEnd)
    {
        $var = $this->commentParser->getVar();

        if ($var !== null) {
            $errorPos = $commentStart + $var->getLine();
            $index = array_keys($this->commentParser->getTagOrders(), 'var');

            if (count($index) > 1) {
                $this->helper->addMessage($errorPos, Helper::DUPLICATE_VAR);
                return;
            }

            if ($index[0] !== 1) {
                $this->helper->addMessage($errorPos, Helper::VAR_ORDER);
            }

            $content = $var->getContent();
            if (empty($content) === true) {
                $this->helper->addMessage($errorPos, Helper::MISSING_VAR_TYPE);
                return;
            } else {
                $suggestedType = $this->helper->suggestType($content);
                if ($content !== $suggestedType) {
                    $data = [$suggestedType, $content];
                    $this->helper->addMessage($errorPos, Helper::INCORRECT_VAR_TYPE, $data);
                } elseif ($this->helper->isAmbiguous($content, $matches)) {
                    // Warn about ambiguous types ie array or mixed
                    $data = [$matches[1], '@var'];
                    $this->helper->addMessage($errorPos, Helper::AMBIGUOUS_TYPE, $data);
                }
            }

            $spacing = substr_count($var->getWhitespaceBeforeContent(), ' ');
            if ($spacing !== 1) {
                $data = [$spacing];
                $this->helper->addMessage($errorPos, Helper::VAR_INDENT, $data);
            }
        } else {
            $this->helper->addMessage($commentEnd, Helper::MISSING_VAR);
        }
    }

    /**
     * Process the see tags.
     *
     * @param int $commentStart The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processSees($commentStart)
    {
        $sees = $this->commentParser->getSees();
        if (empty($sees) === false) {
            foreach ($sees as $see) {
                $errorPos = $commentStart + $see->getLine();
                $content = $see->getContent();
                if (empty($content) === true) {
                    $this->helper->addMessage($errorPos, Helper::EMPTY_SEE, ['variable']);
                    continue;
                }

                $spacing = substr_count($see->getWhitespaceBeforeContent(), ' ');
                if ($spacing !== 1) {
                    $data = [$spacing];
                    $this->helper->addMessage($errorPos, Helper::SEE_INDENT, $data);
                }
            }
        }
    }

    /**
     * Called to process a normal variable.
     *
     * Not required for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int                  $stackPtr  The position where the double quoted
     *                                        string was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
    }

    /**
     * Called to process variables found in double quoted strings.
     *
     * Not required for this sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int                  $stackPtr  The position where the double quoted
     *                                        string was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
    }
}
