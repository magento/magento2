<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Template;

use Magento\Framework\Filter\Template\Tokenizer\Parameter;
use Magento\Framework\Filter\Template\Tokenizer\Variable;

/**
 * Scan an email template for compatibility with the strict resolver
 */
class VariableCompatibilityChecker
{
    private const CONSTRUCTION_DEPEND_PATTERN = '/{{depend\s*(.*?)}}(.*?){{\\/depend\s*}}/si';
    private const CONSTRUCTION_IF_PATTERN = '/{{if\s*(.*?)}}(.*?)({{else}}(.*?))?{{\\/if\s*}}/si';
    private const LOOP_PATTERN = '/{{for(?P<loopItem>.*? )(in)(?P<loopData>.*?)}}(?P<loopBody>.*?){{\/for}}/si';
    private const CONSTRUCTION_PATTERN = '/{{([a-z]{0,10})(.*?)}}(?:(.*?)(?:{{\/(?:\\1)}}))?/si';

    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var Variable
     */
    private Variable $variableTokenizer;

    /**
     * @var Parameter
     */
    private Parameter $parameterTokenizer;

    /**
     * Constructor
     *
     * @param Variable $variableTokenizer
     * @param Parameter $parameterTokenizer
     */
    public function __construct(Variable $variableTokenizer, Parameter $parameterTokenizer)
    {
        $this->variableTokenizer = $variableTokenizer;
        $this->parameterTokenizer = $parameterTokenizer;
    }

    /**
     * Detect invalid usage of template filter directives
     *
     * @param string $template
     */
    public function getCompatibilityIssues(string $template): array
    {
        $this->errors = [];

        if (empty($template)) {
            return [];
        }

        $template = $this->processIfDirectives($template);
        $template = $this->processDependDirectives($template);
        $template = $this->processForDirectives($template);
        $this->processVarDirectivesAndParams($template);

        return $this->errors;
    }

    /**
     * Process the {{if}} directives in the file
     *
     * @param string $html
     * @return string The processed template
     */
    private function processIfDirectives(string $html): string
    {
        if (preg_match_all(self::CONSTRUCTION_IF_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                // validate {{if <var>}}
                $this->validateVariableUsage($construction[1]);
                $html = str_replace($construction[0], $construction[2] . ($construction[4] ?? ''), $html);
            }
        }

        return $html;
    }

    /**
     * Process the {{depend}} directives in the file
     *
     * @param string $html
     * @return string The processed template
     */
    private function processDependDirectives(string $html): string
    {
        if (preg_match_all(self::CONSTRUCTION_DEPEND_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                // validate {{depend <var>}}
                $this->validateVariableUsage($construction[1]);
                $html = str_replace($construction[0], $construction[2], $html);
            }
        }

        return $html;
    }

    /**
     * Process the {{for}} directives in the file
     *
     * @param string $html
     * @return string The processed template
     */
    private function processForDirectives(string $html): string
    {
        if (preg_match_all(self::LOOP_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                // validate {{for in <var>}}
                $this->validateVariableUsage($construction['loopData']);
                $html = str_replace($construction[0], $construction['loopBody'], $html);
            }
        }

        return $html;
    }

    /**
     * Process the all var directives and var directive params in the file
     *
     * @param string $html
     * @return string The processed template
     */
    private function processVarDirectivesAndParams(string $html): string
    {
        if (preg_match_all(self::CONSTRUCTION_PATTERN, $html, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                if (empty($construction[2])) {
                    continue;
                }

                if ($construction[1] === 'var') {
                    $this->validateVariableUsage($construction[2]);
                } else {
                    $this->validateDirectiveBody($construction[2]);
                }
            }
        }

        return $html;
    }

    /**
     * Validate directive body is valid. e.g. {{somedir <directive body>}}
     *
     * @param string $body
     */
    private function validateDirectiveBody(string $body): void
    {
        $this->parameterTokenizer->setString($body);
        $params = $this->parameterTokenizer->tokenize();

        foreach ($params as $param) {
            if (substr($param, 0, 1) === '$') {
                $this->validateVariableUsage(substr($param, 1));
            }
        }
    }

    /**
     * Validate directive variable usage is valid. e.g. {{var <variable body>}} or {{somedir some_param="$foo.bar()"}}
     *
     * @param string $body
     */
    private function validateVariableUsage(string $body): void
    {
        $this->variableTokenizer->setString($body);
        $stack = $this->variableTokenizer->tokenize();

        if (empty($stack)) {
            return;
        }

        foreach ($stack as $token) {
            // As a static analyzer there are no data types to know if this is a DataObject so allow all get* methods
            if ($token['type'] === 'method' && substr($token['name'], 0, 3) !== 'get') {
                $this->addError(
                    'Template directives may not invoke methods. Only scalar array access is allowed.' . PHP_EOL
                    . 'Found "' . trim($body) . '"'
                );
            }
        }
    }

    /**
     * Add an error to the current processing template
     *
     * @param string $error
     */
    private function addError(string $error): void
    {
        $this->errors[] = $error;
    }
}
