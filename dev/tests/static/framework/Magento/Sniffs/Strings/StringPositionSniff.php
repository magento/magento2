<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Strings;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects misusing of IS_IDENTICAL operators when `strpos` and `stripos` functions are used in a condition.
 *
 * Examples:
 * if (!strpos($haystack, $needle)) {}
 * if (stripos($haystack, $needle) == false) {}
 */
class StringPositionSniff implements Sniff
{
    /**
     * String representation of error.
     *
     * @var string
     */
    protected $errorMessage = 'Identical operator === is not used for testing the return value of %s function';

    /**
     * Error violation code.
     *
     * @var string
     */
    protected $errorCode = 'ImproperValueTesting';

    /**
     * Searched functions.
     *
     * @var array
     */
    protected $functions = [
        'strpos',
        'stripos',
    ];

    /**
     * All tokens from current file.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * PHP_CodeSniffer file.
     *
     * @var File
     */
    protected $file;

    /**
     * Left limit for search of identical operators.
     *
     * @var int
     */
    protected $leftLimit;

    /**
     * Right limit for search of identical operators.
     *
     * @var int
     */
    protected $rightLimit;

    /**
     * List of tokens which declares left bound of current scope.
     *
     * @var array
     */
    protected $leftRangeTokens = [
        T_IS_IDENTICAL,
        T_IS_NOT_IDENTICAL,
        T_OPEN_PARENTHESIS,
        T_BOOLEAN_AND,
        T_BOOLEAN_OR,
    ];

    /**
     * List of tokens which declares right bound of current scope.
     *
     * @var array
     */
    protected $rightRangeTokens = [
        T_IS_IDENTICAL,
        T_IS_NOT_IDENTICAL,
        T_CLOSE_PARENTHESIS,
        T_BOOLEAN_AND,
        T_BOOLEAN_OR,
    ];

    /**
     * List of tokens which declares identical operators.
     *
     * @var array
     */
    protected $identical = [
        T_IS_IDENTICAL,
        T_IS_NOT_IDENTICAL,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_IF, T_ELSEIF];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $this->tokens = $phpcsFile->getTokens();
        $this->file = $phpcsFile;
        $this->leftLimit = $open = $this->tokens[$stackPtr]['parenthesis_opener'];
        $this->rightLimit = $close = $this->tokens[$stackPtr]['parenthesis_closer'];
        for ($i = ($open + 1); $i < $close; $i++) {
            if (($this->tokens[$i]['code'] === T_STRING && in_array($this->tokens[$i]['content'], $this->functions))
                && (!$this->findIdentical($i - 1, $this->findFunctionParenthesisCloser($i) + 1))
            ) {
                $foundFunctionName = $this->tokens[$i]['content'];
                $phpcsFile->addError($this->errorMessage, $i, $this->errorCode, [$foundFunctionName]);
            }
        }
    }

    /**
     * Recursively finds identical operators in current scope.
     *
     * @param int $leftCurrentPosition
     * @param int $rightCurrentPosition
     * @return bool
     */
    protected function findIdentical($leftCurrentPosition, $rightCurrentPosition)
    {
        $leftBound = $this->file->findPrevious($this->leftRangeTokens, $leftCurrentPosition, $this->leftLimit - 1);
        $rightBound = $this->file->findNext($this->rightRangeTokens, $rightCurrentPosition, $this->rightLimit + 1);
        $leftToken = $this->tokens[$leftBound];
        $rightToken = $this->tokens[$rightBound];
        if ($leftToken['code'] === T_OPEN_PARENTHESIS && $rightToken['code'] === T_CLOSE_PARENTHESIS) {
            return $this->findIdentical($leftBound - 1, $rightBound + 1);
        } else {
            return (
                in_array($leftToken['code'], $this->identical) || in_array($rightToken['code'], $this->identical)
            ) ?: false;
        }
    }

    /**
     * Finds the position of close parenthesis of detected function.
     *
     * @param int $currentPosition
     * @return mixed
     */
    protected function findFunctionParenthesisCloser($currentPosition)
    {
        $nextOpenParenthesis = $this->file->findNext(T_OPEN_PARENTHESIS, $currentPosition, $this->rightLimit);
        return $nextOpenParenthesis ? $this->tokens[$nextOpenParenthesis]['parenthesis_closer'] : false;
    }
}
