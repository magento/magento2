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
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Resolves variables the way that they have traditionally been resolved and allows method execution
 */
class LegacyResolver implements VariableResolverInterface
{
    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var VariableFactory
     */
    private $variableTokenizerFactory;

    /**
     * @param StringUtils $stringUtils
     * @param VariableFactory $variableTokenizerFactory
     */
    public function __construct(StringUtils $stringUtils, VariableFactory $variableTokenizerFactory)
    {
        $this->stringUtils = $stringUtils;
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
            if ($i == 0 && isset($templateVariables[$stackArgs[$i]['name']])) {
                // Getting of template value
                $stackArgs[$i]['variable'] = &$templateVariables[$stackArgs[$i]['name']];
            } elseif ($this->shouldHandleDataAccess($i, $stackArgs)) {
                $this->handleDataAccess($i, $filter, $templateVariables, $stackArgs);
                $last = $i;
            } elseif ($this->shouldHandleAsObjectAccess($i, $stackArgs)) {
                $this->handleObjectMethod($filter, $templateVariables, $i, $stackArgs);
                $last = $i;
            }
        }

        if (isset($stackArgs[$last]['variable'])) {
            // If value for construction exists set it
            $result = $stackArgs[$last]['variable'];
        }

        return $result;
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
     * Handle the access of a variable's property at an index
     *
     * @param int $i
     * @param array $stackArgs
     */
    private function handlePropertyAccess(int $i, array &$stackArgs): void
    {
        if (is_array($stackArgs[$i - 1]['variable'])) {
            $stackArgs[$i]['variable'] = $stackArgs[$i - 1]['variable'][$stackArgs[$i]['name']];
        } else {
            $caller = 'get' . $this->stringUtils->upperCaseWords($stackArgs[$i]['name'], '_', '');
            $stackArgs[$i]['variable'] = method_exists(
                $stackArgs[$i - 1]['variable'],
                $caller
            ) ? $stackArgs[$i - 1]['variable']->{$caller}() : $stackArgs[$i - 1]['variable']->getData(
                $stackArgs[$i]['name']
            );
        }
    }

    /**
     * Handle the calling of a DataObject's method at an index
     *
     * @param Template $filter
     * @param array $templateVariables
     * @param int $i
     * @param array $stackArgs
     */
    private function handleDataObjectMethod(
        Template $filter,
        array $templateVariables,
        int $i,
        array &$stackArgs
    ): void {
        if (method_exists($stackArgs[$i - 1]['variable'], $stackArgs[$i]['name'])
            || substr($stackArgs[$i]['name'], 0, 3) == 'get'
        ) {
            $stackArgs[$i]['args'] = $this->getStackArgs(
                $stackArgs[$i]['args'],
                $filter,
                $templateVariables
            );

            $stackArgs[$i]['variable'] = call_user_func_array(
                [$stackArgs[$i - 1]['variable'], $stackArgs[$i]['name']],
                $stackArgs[$i]['args']
            );
        }
    }

    /**
     * Handle the calling of an arbitrary object method
     *
     * @param Template $filter
     * @param array $templateVariables
     * @param int $i
     * @param array $stackArgs
     */
    private function handleObjectMethod(Template $filter, array $templateVariables, int $i, array &$stackArgs): void
    {
        $object = $stackArgs[$i - 1]['variable'];
        $method = $stackArgs[$i]['name'];
        if (method_exists($object, $method) && substr($method, 0, 3) !== 'set') {
            $args = $this->getStackArgs($stackArgs[$i]['args'], $filter, $templateVariables);
            $stackArgs[$i]['variable'] = call_user_func_array([$object, $method], $args);
        }
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
                || is_array($stackArgs[$i - 1]['variable'])
            );
    }

    /**
     * Return if the given index should be processed for object access
     *
     * @param int $i
     * @param array $stackArgs
     * @return bool
     */
    private function shouldHandleAsObjectAccess(int $i, array &$stackArgs): bool
    {
        return isset($stackArgs[$i - 1]['variable'])
            && is_object($stackArgs[$i - 1]['variable'])
            && $stackArgs[$i]['type'] == 'method';
    }

    /**
     * Handle the intended access of data at the given stack arg index
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
            $this->handlePropertyAccess($i, $stackArgs);
        } elseif ($stackArgs[$i]['type'] == 'method') {
            $this->handleDataObjectMethod($filter, $templateVariables, $i, $stackArgs);
        }
    }
}
