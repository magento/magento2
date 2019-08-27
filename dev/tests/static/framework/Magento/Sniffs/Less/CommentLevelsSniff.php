<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class CommentLevelsSniff
 *
 * First and second level comments must be surrounded by empty lines.
 * First, second and third level comments should have two spaces after "//".
 * Inline comments should have one space after "//".
 *
 * @link https://devdocs.magento.com/guides/v2.3/coding-standards/code-standard-less.html#comments
 */
class CommentLevelsSniff implements Sniff
{
    const COMMENT_STRING = '//';

    const FIRST_LEVEL_COMMENT = '_____________________________________________';

    const SECOND_LEVEL_COMMENT = '--';

    /**
     * @var array
     */
    protected $levelComments = [
        self::FIRST_LEVEL_COMMENT   => T_STRING,
        self::SECOND_LEVEL_COMMENT  => T_DEC,
    ];

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ((T_STRING !== $tokens[$stackPtr]['code'])
            || (self::COMMENT_STRING !== $tokens[$stackPtr]['content'])
            || (1 === $tokens[$stackPtr]['line'])
        ) {
            return;
        }

        $textInSameLine = $phpcsFile->findPrevious([T_STRING, T_STYLE], $stackPtr - 1);

        // is inline comment
        if ((false !== $textInSameLine)
            && ($tokens[$textInSameLine]['line'] === $tokens[$stackPtr]['line'])
        ) {
            $this->validateInlineComment($phpcsFile, $stackPtr, $tokens);
            return;
        }

        $this->validateCommentLevel($phpcsFile, $stackPtr, $tokens);

        if (!$this->isNthLevelComment($phpcsFile, $stackPtr, $tokens)) {
            return;
        }

        if (!$this->checkNthLevelComment($phpcsFile, $stackPtr, $tokens)) {
            $phpcsFile->addError(
                'First and second level comments must be surrounded by empty lines',
                $stackPtr,
                'SpaceMissed'
            );
        }
    }

    /**
     * Validate that inline comment responds to given requirements
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @return bool
     */
    private function validateInlineComment(File $phpcsFile, $stackPtr, array $tokens)
    {
        if ($tokens[$stackPtr + 1]['content'] !== TokenizerSymbolsInterface::WHITESPACE) {
            $phpcsFile->addError('Inline comment should have 1 space after "//"', $stackPtr, 'SpaceMissedAfter');
        }
        if ($tokens[$stackPtr - 1]['content'] !== TokenizerSymbolsInterface::WHITESPACE) {
            $phpcsFile->addError('Inline comment should have 1 space before "//"', $stackPtr, 'SpaceMissedBefore');
        }
    }

    /**
     * Check is it n-th level comment was found
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @return bool
     */
    private function isNthLevelComment(File $phpcsFile, $stackPtr, array $tokens)
    {
        $nthLevelCommentFound = false;
        $levelComment = 0;

        foreach ($this->levelComments as $code => $comment) {
            $levelComment = $phpcsFile->findNext($comment, $stackPtr, null, false, $code);
            if (false !== $levelComment) {
                $nthLevelCommentFound = true;
                break;
            }
        }

        if (false === $nthLevelCommentFound) {
            return false;
        }

        $currentLine = $tokens[$stackPtr]['line'];
        $levelCommentLine = $tokens[$levelComment]['line'];

        if ($currentLine !== $levelCommentLine) {
            return false;
        }

        return true;
    }

    /**
     * Check is it n-th level comment is correct
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     * @return bool
     */
    private function checkNthLevelComment(File $phpcsFile, $stackPtr, array $tokens)
    {
        $correct = false;

        $nextLine = $phpcsFile->findNext(
            T_WHITESPACE,
            $stackPtr,
            null,
            false,
            TokenizerSymbolsInterface::NEW_LINE
        );

        if (false === $nextLine) {
            return $correct;
        }

        if (($tokens[$nextLine]['content'] !== TokenizerSymbolsInterface::NEW_LINE)
            || ($tokens[$nextLine + 1]['content'] !== TokenizerSymbolsInterface::NEW_LINE)
        ) {
            return $correct;
        }

        $commentLinePtr = $stackPtr;
        while ($tokens[$commentLinePtr - 2]['line'] > 1) {
            $commentLinePtr = $phpcsFile->findPrevious(T_STRING, $commentLinePtr - 1, null, false, '//');

            if (false === $commentLinePtr) {
                continue;
            }

            if (($tokens[$commentLinePtr - 1]['content'] === TokenizerSymbolsInterface::NEW_LINE)
                && ($tokens[$commentLinePtr - 2]['content'] === TokenizerSymbolsInterface::NEW_LINE)
            ) {
                $correct = true;
                break;
            }
        }

        return $correct;
    }

    /**
     * Validation of comment level.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array $tokens
     */
    private function validateCommentLevel(File $phpcsFile, int $stackPtr, array $tokens): void
    {
        if ($tokens[$stackPtr + 2]['content'] !== 'magento_import' &&
            !in_array(
                $tokens[$stackPtr + 1]['content'],
                [
                    TokenizerSymbolsInterface::DOUBLE_WHITESPACE,
                    TokenizerSymbolsInterface::NEW_LINE,
                ],
                true
            )
        ) {
            $phpcsFile->addError('Level\'s comment does not have 2 spaces after "//"', $stackPtr, 'SpacesMissed');
        }
    }
}
