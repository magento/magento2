<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\VariableResolver;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\Tokenizer\VariableFactory;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Resolves variables allowing only scalar values
 */
class StrictResolver implements VariableResolverInterface
{
    private $stackArgs;

    /**
     * @var VariableFactory
     */
    private $variableTokenizerFactory;

    /**
     * @param VariableFactory $variableTokenizerFactory
     */
    public function __construct(VariableFactory $variableTokenizerFactory)
    {
        $this->variableTokenizerFactory = $variableTokenizerFactory;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $value, Template $filter, array $templateVariables)
    {
        if (empty($value)) {
            return null;
        }

        $tokenizer = $this->variableTokenizerFactory->create();
        $tokenizer->setString($value);
        $this->stackArgs = $tokenizer->tokenize();
        $result = null;
        $last = 0;
        for ($i = 0, $count = count($this->stackArgs); $i < $count; $i++) {
            if ($i === 0 && isset($templateVariables[$this->stackArgs[$i]['name']])) {
                // Getting of template value
                $this->stackArgs[$i]['variable'] = &$templateVariables[$this->stackArgs[$i]['name']];
            } elseif ($this->shouldHandleDataAccess($i)) {
                $this->handleDataAccess($i);

                $last = $i;
            }
        }

        if (isset($this->stackArgs[$last]['variable'])) {
            // If value for construction exists set it
            $result = $this->stackArgs[$last]['variable'];
        }

        return $result;
    }

    /**
     * Handle variable access at a given index
     *
     * @param int $i
     */
    private function handleDataAccess(int $i): void
    {
        // If data object calling methods or getting properties
        if ($this->stackArgs[$i]['type'] == 'property') {
            if (is_array($this->stackArgs[$i - 1]['variable'])) {
                $this->stackArgs[$i]['variable'] = $this->stackArgs[$i - 1]['variable'][$this->stackArgs[$i]['name']];
            } else {
                // Strict mode should not call getter methods except DataObject's getData
                $this->stackArgs[$i]['variable'] = $this->stackArgs[$i - 1]['variable']
                    ->getData($this->stackArgs[$i]['name']);
            }
        } elseif ($this->stackArgs[$i]['type'] == 'method' && substr($this->stackArgs[$i]['name'], 0, 3) == 'get') {
            $dataKey = $this->extractDataKeyFromGetter($this->stackArgs[$i]['name']);
            $this->stackArgs[$i]['variable'] = $this->stackArgs[$i - 1]['variable']->getData($dataKey);
        }
    }

    /**
     * Extract the DataObject key name from a getter method name in the same way as DataObject does internally
     *
     * @param string $method
     * @return string
     */
    private function extractDataKeyFromGetter(string $method)
    {
        return strtolower(ltrim(trim(preg_replace('/([A-Z]|[0-9]+)/', '_$1', substr($method, 3))), '_'));
    }

    /**
     * Return if the given index should be processed for data access
     *
     * @param int $i
     * @return bool
     */
    private function shouldHandleDataAccess(int $i): bool
    {
        return isset($this->stackArgs[$i - 1]['variable'])
            && (
                $this->stackArgs[$i - 1]['variable'] instanceof DataObject
                || is_array($this->stackArgs[$i - 1]['variable'])
            );
    }
}
