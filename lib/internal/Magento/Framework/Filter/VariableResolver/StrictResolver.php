<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\VariableResolver;

use Magento\Email\Model\AbstractTemplate;
use Magento\Framework\DataObject;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\Tokenizer\VariableFactory;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Resolves variables allowing only scalar values
 */
class StrictResolver implements VariableResolverInterface
{
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
        $stackArgs = $tokenizer->tokenize();
        $result = null;
        $last = 0;
        for ($i = 0, $count = count($stackArgs); $i < $count; $i++) {
            if ($i === 0 && isset($templateVariables[$stackArgs[$i]['name']])) {
                // Getting of template value
                $stackArgs[$i]['variable'] = &$templateVariables[$stackArgs[$i]['name']];
            } elseif ($this->shouldHandleDataAccess($i, $stackArgs)) {
                $this->handleDataAccess($i, $filter, $templateVariables, $stackArgs);

                $last = $i;
            }
        }

        if (isset($stackArgs[$last]['variable'])
            && (is_scalar($stackArgs[$last]['variable']) || is_array($stackArgs[$last]['variable']))
        ) {
            // If value for construction exists set it
            $result = $stackArgs[$last]['variable'];
        }

        return $result;
    }

    /**
     * Handle variable access at a given index
     *
     * @param int $i
     * @param Template $filter
     * @param array $templateVariables
     * @param array $stackArgs
     */
    private function handleDataAccess(int $i, Template $filter, array $templateVariables, array &$stackArgs): void
    {
        // If data object calling methods or getting properties
        if ($stackArgs[$i]['type'] == 'property') {
            if (is_array($stackArgs[$i - 1]['variable'])) {
                $stackArgs[$i]['variable'] = $stackArgs[$i - 1]['variable'][$stackArgs[$i]['name']];
            } else {
                // Strict mode should not call getter methods except DataObject's getData
                $stackArgs[$i]['variable'] = $stackArgs[$i - 1]['variable']
                    ->getData($stackArgs[$i]['name']);
            }
        } elseif ($stackArgs[$i]['type'] == 'method' && substr($stackArgs[$i]['name'] ?? '', 0, 3) == 'get') {
            $this->handleGetterMethod($i, $filter, $templateVariables, $stackArgs);
        }
    }

    /**
     * Handle getter method access at a given stack index
     *
     * @param int $i
     * @param Template $filter
     * @param array $templateVariables
     * @param array $stackArgs
     */
    private function handleGetterMethod(int $i, Template $filter, array $templateVariables, array &$stackArgs): void
    {
        if ($stackArgs[$i]['name'] === 'getUrl'
            && $stackArgs[$i - 1]['variable'] instanceof AbstractTemplate
        ) {
            $stackArgs[$i]['args'] = $this->getStackArgs(
                $stackArgs[$i]['args'],
                $filter,
                $templateVariables
            );

            $stackArgs[$i]['args'][0] = $templateVariables['store'];
            $stackArgs[$i]['variable'] = $stackArgs[$i - 1]['variable']->getUrl(
                ...$stackArgs[$i]['args']
            );
        } else {
            $dataKey = $this->extractDataKeyFromGetter($stackArgs[$i]['name']);
            $stackArgs[$i]['variable'] = $stackArgs[$i - 1]['variable']->getData($dataKey);
        }
    }

    /**
     * Loops over a set of stack args to process variables into array argument values
     *
     * @param array $stack
     * @param Template $filter
     * @param array $templateVariables
     * @return array
     */
    private function getStackArgs($stack, Template $filter, array $templateVariables): array
    {
        foreach ($stack as $i => $value) {
            if (is_array($value)) {
                $stack[$i] = $this->getStackArgs($value, $filter, $templateVariables);
            } elseif (substr((string)$value, 0, 1) === '$') {
                $stack[$i] = $this->resolve(substr($value, 1), $filter, $templateVariables);
            }
        }

        return $stack;
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
     * @param array $stackArgs
     * @return bool
     */
    private function shouldHandleDataAccess(int $i, array &$stackArgs): bool
    {
        return isset($stackArgs[$i - 1]['variable'])
            && (
                $stackArgs[$i - 1]['variable'] instanceof DataObject
                || $stackArgs[$i - 1]['variable'] instanceof AbstractTemplate
                || is_array($stackArgs[$i - 1]['variable'])
            );
    }
}
