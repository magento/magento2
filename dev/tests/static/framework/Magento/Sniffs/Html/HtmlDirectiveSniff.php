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
     * @var array
     */
    private $usedVariables = [];

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

        $this->usedVariables = [];

        if (preg_match_all(Template::CONSTRUCTION_IF_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                // validate {{if <var>}}
                $this->validateVariableUsage($phpcsFile, $construction[1]);
                $html = str_replace($construction[0], $construction[2] . ($construction[4] ?? ''), $html);
            }
        }

        if (preg_match_all(Template::CONSTRUCTION_DEPEND_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                // validate {{depend <var>}}
                $this->validateVariableUsage($phpcsFile, $construction[1]);
                $html = str_replace($construction[0], $construction[2], $html);
            }
        }

        if (preg_match_all(Template::LOOP_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                // validate {{for in <var>}}
                $this->validateVariableUsage($phpcsFile, $construction['loopData']);
                $html = str_replace($construction[0], $construction['loopBody'], $html);
            }
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

        $this->validateDefinedVariables($phpcsFile, $html);
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
                $this->validateVariableUsage($phpcsFile, substr($param, 1));
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
        $this->usedVariables[] = 'var ' . trim($body);
        $variableTokenizer = new Template\Tokenizer\Variable();
        $variableTokenizer->setString($body);
        $stack = $variableTokenizer->tokenize();

        if (empty($stack)) {
            return;
        }

        foreach ($stack as $token) {
            // As a static analyzer there are no data types to know if this is a DataObject so allow all get* methods
            if ($token['type'] === 'method' && substr($token['name'], 0, 3) !== 'get') {
                $phpcsFile->addError(
                    'Template directives may not invoke methods. Only scalar array access is allowed.' . PHP_EOL
                    . 'Found "' . trim($body) . '"',
                    null,
                    'HtmlTemplates.DirectiveUsage.ProhibitedMethodCall'
                );
            }
        }
    }

    /**
     * Validate the variables defined in the template comment block match the variables actually used in the template
     *
     * @param File $phpcsFile
     * @param string $templateText
     */
    private function validateDefinedVariables(File $phpcsFile, string $templateText): void
    {
        preg_match('/<!--@vars\s*((?:.)*?)\s*@-->/us', $templateText, $matches);

        $definedVariables = [];

        if (!empty($matches[1])) {
            $definedVariables = json_decode(str_replace("\n", '', $matches[1]), true);
            if (json_last_error()) {
                $phpcsFile->addError(
                    'Template @vars comment block contains invalid JSON.',
                    null,
                    'HtmlTemplates.DirectiveUsage.InvalidVarsJSON'
                );
                return;
            }

            foreach ($definedVariables as $var => $label) {
                if (empty($label)) {
                    $phpcsFile->addError(
                        'Template @vars comment block contains invalid label.' . PHP_EOL
                        . 'Label for variable "' . $var . '" is empty.',
                        null,
                        'HtmlTemplates.DirectiveUsage.InvalidVariableLabel'
                    );
                }
            }

            $definedVariables = array_keys($definedVariables);
        }

        $undefinedVariables = array_diff($this->usedVariables, $definedVariables);
        foreach ($undefinedVariables as $undefinedVariable) {
            $phpcsFile->addError(
                'Template @vars comment block is missing a variable used in the template.' . PHP_EOL
                 . 'Missing variable: ' . $undefinedVariable,
                null,
                'HtmlTemplates.DirectiveUsage.UndefinedVariable'
            );
        }

        $extraDefinedVariables = array_diff($definedVariables, $this->usedVariables);
        foreach ($extraDefinedVariables as $extraDefinedVariable) {
            if (substr($extraDefinedVariable, 0, 4) !== 'var ') {
                continue;
            }
            $phpcsFile->addError(
                'Template @vars comment block contains a variable not used in the template.' . PHP_EOL
                 . 'Extra variable: ' . $extraDefinedVariable,
                null,
                'HtmlTemplates.DirectiveUsage.ExtraVariable'
            );
        }
    }
}
