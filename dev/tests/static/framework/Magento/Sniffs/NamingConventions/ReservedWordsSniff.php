<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\NamingConventions;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

class ReservedWordsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * source: http://php.net/manual/en/reserved.other-reserved-words.php
     *
     * @var array PHP 7 reserved words for name spaces
     */
    protected $reservedWords = [
        'int',
        'float',
        'bool',
        'string',
        'true',
        'false',
        'null',
        'resource',
        'object',
        'mixed',
        'numeric',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_NAMESPACE, T_CLASS];
    }

    /**
     * Check all namespace parts
     *
     * @param PHP_CodeSniffer_File $sourceFile
     * @param int $stackPtr
     * @return void
     */
    protected function validateNameSpace(PHP_CodeSniffer_File $sourceFile, $stackPtr)
    {
        $skippedTokens = ['T_NS_SEPARATOR', 'T_WHITESPACE'];
        //skip "namespace" and whitespace
        $stackPtr += 2;
        $tokens = $sourceFile->getTokens();
        while ('T_SEMICOLON' != $tokens[$stackPtr]['type']) {
            if (in_array($tokens[$stackPtr]['type'], $skippedTokens)) {
                $stackPtr++;
                continue;
            }
            $nameSpacePart = strtolower($tokens[$stackPtr]['content']);
            if (in_array($nameSpacePart, $this->reservedWords)) {
                $sourceFile->addError('\'' . $nameSpacePart . '\' is a reserved word in PHP 7.', $stackPtr);
            }
            $stackPtr++;
        }
    }

    /**
     * Check class name not having reserved words
     *
     * @param PHP_CodeSniffer_File $sourceFile
     * @param int $stackPtr
     * @return void
     */
    protected function validateClass(PHP_CodeSniffer_File $sourceFile, $stackPtr)
    {
        $tokens = $sourceFile->getTokens();
        //skipped "class" and whitespace
        $stackPtr += 2;
        $className = strtolower($tokens[$stackPtr]['content']);

        if (in_array($className, $this->reservedWords)) {
            $sourceFile->addError('Class name \'' . $className . '\' is a reserved word in PHP 7', $stackPtr);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $sourceFile, $stackPtr)
    {
        $tokens = $sourceFile->getTokens();
        switch ($tokens[$stackPtr]['type']) {
            case "T_CLASS":
                $this->validateClass($sourceFile, $stackPtr);
                break;
            case "T_NAMESPACE":
                $this->validateNameSpace($sourceFile, $stackPtr);
                break;
        }
    }
}
