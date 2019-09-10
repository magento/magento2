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
     * @var string[]
     */
    private $restrictedMethods = [
        'addafterfiltercallback',
        'getresourcecollection',
        'load',
        'save',
        'getcollection',
        'getresource',
        'getconfig',
        'setvariables',
        'settemplateprocessor',
        'gettemplateprocessor',
        'vardirective',
        'delete'
    ];

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var array
     */
    private $stackArgs;

    /**
     * @var VariableFactory
     */
    private $variableTokenizerFactory;

    /**
     * Stack of stacks for recursive variable resolution
     *
     * @var array
     */
    private $storedStacks = [];

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
        $this->stackArgs = $tokenizer->tokenize();
        $result = null;
        $last = 0;
        for ($i = 0, $count = count($this->stackArgs); $i < $count; $i++) {
            if ($i == 0 && isset($templateVariables[$this->stackArgs[$i]['name']])) {
                // Getting of template value
                $this->stackArgs[$i]['variable'] = &$templateVariables[$this->stackArgs[$i]['name']];
            } elseif ($this->shouldHandleDataAccess($i)) {
                $this->handleDataAccess($i, $filter, $templateVariables);
                $last = $i;
            } elseif ($this->shouldHandleAsObjectAccess($i)) {
                $this->handleObjectMethod($filter, $templateVariables, $i);
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
     * Validate method call initiated in a template.
     *
     * Deny calls for methods that may disrupt template processing.
     *
     * @param object $object
     * @param Template $filter
     * @param string $method
     * @return void
     */
    private function validateVariableMethodCall($object, Template $filter, string $method): void
    {
        if ($object === $filter) {
            if (in_array(mb_strtolower($method), $this->restrictedMethods)) {
                throw new \InvalidArgumentException("Method $method cannot be called from template.");
            }
        }
    }

    /**
     * Check allowed methods for data objects.
     *
     * Deny calls for methods that may disrupt template processing.
     *
     * @param object $object
     * @param string $method
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function isAllowedDataObjectMethod($object, string $method): bool
    {
        if ($object instanceof AbstractExtensibleModel || $object instanceof AbstractModel) {
            if (in_array(mb_strtolower($method), $this->restrictedMethods)) {
                throw new \InvalidArgumentException("Method $method cannot be called from template.");
            }
        }

        return true;
    }

    /**
     * Loops over a set of stack args to process variables into array argument values
     *
     * @param array $stack
     * @param Template $filter
     * @param array $templateVariables
     * @return array
     */
    private function getStackArgs($stack, Template $filter, array $templateVariables)
    {
        foreach ($stack as $i => $value) {
            if (is_array($value)) {
                $stack[$i] = $this->getStackArgs($value, $filter, $templateVariables);
            } elseif (substr((string)$value, 0, 1) === '$') {
                $this->storedStacks[] = $this->stackArgs;
                $stack[$i] = $this->resolve(substr($value, 1), $filter, $templateVariables);
                $this->stackArgs = array_pop($this->storedStacks);
            }
        }

        return $stack;
    }

    /**
     * Handle the access of a variable's property at an index
     *
     * @param int $i
     */
    private function handlePropertyAccess(int $i): void
    {
        if (is_array($this->stackArgs[$i - 1]['variable'])) {
            $this->stackArgs[$i]['variable'] = $this->stackArgs[$i - 1]['variable'][$this->stackArgs[$i]['name']];
        } else {
            $caller = 'get' . $this->stringUtils->upperCaseWords($this->stackArgs[$i]['name'], '_', '');
            $this->stackArgs[$i]['variable'] = method_exists(
                $this->stackArgs[$i - 1]['variable'],
                $caller
            ) ? $this->stackArgs[$i - 1]['variable']->{$caller}() : $this->stackArgs[$i - 1]['variable']->getData(
                $this->stackArgs[$i]['name']
            );
        }
    }

    /**
     * Handle the calling of a DataObject's method at an index
     *
     * @param Template $filter
     * @param array $templateVariables
     * @param int $i
     */
    private function handleDataObjectMethod(Template $filter, array $templateVariables, int $i): void
    {
        if (method_exists($this->stackArgs[$i - 1]['variable'], $this->stackArgs[$i]['name'])
            || substr($this->stackArgs[$i]['name'], 0, 3) == 'get'
        ) {
            $this->stackArgs[$i]['args'] = $this->getStackArgs(
                $this->stackArgs[$i]['args'],
                $filter,
                $templateVariables
            );

            if ($this->isAllowedDataObjectMethod($this->stackArgs[$i - 1]['variable'], $this->stackArgs[$i]['name'])) {
                $this->stackArgs[$i]['variable'] = call_user_func_array(
                    [$this->stackArgs[$i - 1]['variable'], $this->stackArgs[$i]['name']],
                    $this->stackArgs[$i]['args']
                );
            }
        }
    }

    /**
     * Handle the calling of an arbitrary object method
     *
     * @param Template $filter
     * @param array $templateVariables
     * @param int $i
     */
    private function handleObjectMethod(Template $filter, array $templateVariables, int $i): void
    {
        $object = $this->stackArgs[$i - 1]['variable'];
        $method = $this->stackArgs[$i]['name'];
        if (method_exists($object, $method)) {
            $args = $this->getStackArgs($this->stackArgs[$i]['args'], $filter, $templateVariables);
            $this->validateVariableMethodCall($object, $filter, $method);
            $this->stackArgs[$i]['variable'] = call_user_func_array([$object, $method], $args);
        }
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

    /**
     * Return if the given index should be processed for object access
     *
     * @param int $i
     * @return bool
     */
    private function shouldHandleAsObjectAccess(int $i): bool
    {
        return isset($this->stackArgs[$i - 1]['variable'])
            && is_object($this->stackArgs[$i - 1]['variable'])
            && $this->stackArgs[$i]['type'] == 'method';
    }

    /**
     * Handle the intended access of data at the given stack arg index
     *
     * @param int $i
     * @param Template $filter
     * @param array $templateVariables
     */
    private function handleDataAccess(int $i, Template $filter, array $templateVariables): void
    {
        // If data object calling methods or getting properties
        if ($this->stackArgs[$i]['type'] == 'property') {
            $this->handlePropertyAccess($i);
        } elseif ($this->stackArgs[$i]['type'] == 'method') {
            $this->handleDataObjectMethod($filter, $templateVariables, $i);
        }
    }
}
