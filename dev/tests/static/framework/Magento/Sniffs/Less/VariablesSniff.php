<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sniffs\Less;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Class VariablesSniff
 *
 * Ensure that the variables are responds to the following requirements:
 * - If variables are local and used only in a module scope,
 *   they should be located in the module file, in the beginning of the general comment.
 * - All variable names must be lowercase
 *
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#local-variables
 * @link http://devdocs.magento.com/guides/v2.0/coding-standards/code-standard-less.html#naming
 *
 */
class VariablesSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [TokenizerSymbolsInterface::TOKENIZER_CSS];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_ASPERAND];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $currentToken = $tokens[$stackPtr];

        $nextColon = $phpcsFile->findNext(T_COLON, $stackPtr);
        $nextSemicolon = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        if ((false === $nextColon) || (false === $nextSemicolon)) {
            return;
        }

        $isVariableDeclaration = ($currentToken['line'] === $tokens[$nextColon]['line'])
            && ($currentToken['line'] === $tokens[$nextSemicolon]['line'])
            && (T_STRING === $tokens[$stackPtr + 1]['code'])
            && (T_COLON === $tokens[$stackPtr + 2]['code']);

        if (!$isVariableDeclaration) {
            return;
        }

        $classBefore = $phpcsFile->findPrevious(T_STYLE, $stackPtr);
        if (false !== $classBefore) {
            $phpcsFile->addError(
                'Variable declaration located not in the beginning of general comments',
                $stackPtr,
                'VariableLocation'
            );
        }

        $variableName = $tokens[$stackPtr + 1]['content'];
        if (preg_match('/[A-Z]/', $variableName)) {
            $phpcsFile->addError(
                'Variable declaration contains uppercase symbols',
                $stackPtr,
                'VariableUppercase'
            );
        }
    }
}
