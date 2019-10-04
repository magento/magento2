<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sniffs\Html;

use Magento\Framework\Filter\Template;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniff for invalid directive usage in HTML templates
 */
class HtmlDirectiveSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        return [T_INLINE_HTML];
    }

    /**
     * Detect invalid usage of template filter directives
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return int|void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if ($stackPtr !== 0) {
            return;
        }

        $html = $phpcsFile->getTokensAsString($stackPtr, count($phpcsFile->getTokens()));

        if (empty($html)) {
            return;
        }

        if (preg_match_all(Template::CONSTRUCTION_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                if (empty($construction[2])) {
                    continue;
                }

                if ($construction[1] === 'var') {
                    $this->validateVariableUsage($phpcsFile, $construction[2]);
                } else {
                    $this->validateDirectiveBody($phpcsFile, $construction[2]);
                }
            }
        }
    }

    /**
     * Validate directive body is valid. e.g. {{somedir <directive body>}}
     *
     * @param File $phpcsFile
     * @param string $body
     */
    private function validateDirectiveBody(File $phpcsFile, string $body): void
    {
        $parameterTokenizer = new Template\Tokenizer\Parameter();
        $parameterTokenizer->setString($body);
        $params = $parameterTokenizer->tokenize();

        foreach ($params as $param) {
            if (substr($param, 0, 1) === '$') {
                $this->validateVariableUsage($phpcsFile, $param);
            }
        }
    }

    /**
     * Validate directive variable usage is valid. e.g. {{var <variable body>}} or {{somedir some_param="$foo.bar()"}}
     *
     * @param File $phpcsFile
     * @param string $body
     */
    private function validateVariableUsage(File $phpcsFile, string $body): void
    {
        $variableTokenizer = new Template\Tokenizer\Variable();
        $variableTokenizer->setString($body);
        $stack = $variableTokenizer->tokenize();

        if (empty($stack)) {
            return;
        }

        foreach ($stack as $token) {
            if ($token['type'] === 'method') {
                $phpcsFile->addError(
                    'Template directives may not invoke methods. Only scalar array access is allowed.' . PHP_EOL
                    . 'Found "' . trim($body) . '"',
                    null,
                    'HtmlTemplates.DirectiveUsage.ProhibitedMethodCall'
                );
            }
        }
    }
}
