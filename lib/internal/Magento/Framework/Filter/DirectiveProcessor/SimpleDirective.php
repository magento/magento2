<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\Filter\DirectiveProcessor\Filter\FilterApplier;
use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\SimpleDirective\ProcessorPool;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\Tokenizer\Parameter;
use Magento\Framework\Filter\Template\Tokenizer\ParameterFactory;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Serves as the default
 */
class SimpleDirective implements DirectiveProcessorInterface
{
    /**
     * @var ProcessorPool
     */
    private $processorPool;

    /**
     * @var ParameterFactory
     */
    private $parameterTokenizerFactory;

    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;
    /**
     * @var FilterApplier
     */
    private $filterApplier;

    /**
     * @param ProcessorPool $processorPool
     * @param ParameterFactory $parameterTokenizerFactory
     * @param VariableResolverInterface $variableResolver
     * @param FilterApplier $filterApplier
     */
    public function __construct(
        ProcessorPool $processorPool,
        ParameterFactory $parameterTokenizerFactory,
        VariableResolverInterface $variableResolver,
        FilterApplier $filterApplier
    ) {
        $this->processorPool = $processorPool;
        $this->parameterTokenizerFactory = $parameterTokenizerFactory;
        $this->variableResolver = $variableResolver;
        $this->filterApplier = $filterApplier;
    }

    /**
     * @inheritdoc
     */
    public function process(array $construction, Template $filter, array $templateVariables): string
    {
        try {
            $directiveParser = $this->processorPool
                ->get($construction['directiveName']);
        } catch (\InvalidArgumentException $e) {
            // This directive doesn't have a SimpleProcessor
            return $construction[0];
        }

        $parameters = $this->extractParameters($construction, $filter, $templateVariables);

        $value = $directiveParser->process(
            $construction['value'] ?? null,
            $parameters,
            !empty($construction['content']) ? $filter->filter($construction['content']) : null
        );

        $value = $this->filterApplier->applyFromRawParam(
            $construction['filters'] ?? '',
            $value,
            $directiveParser->getDefaultFilters() ?? []
        );

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getRegularExpression(): string
    {
        return '/{{'
        . '(?P<directiveName>[a-z]+)(?:[\s\t]*)'
        . '(?:\s*(?P<quoteType>[\'"])(?P<value>(?:(?!\k\'quoteType\').)*?)(?<!\\\)\k\'quoteType\')?'
        . '(?P<parameters>.*?)'
        . '(?P<filters>(?:\|[a-z0-9:_-]+)+)?'
        . '}}'
        . '(?:(?P<content>.*?){{\/(?P=directiveName)}})?'
        . '/si';
    }

    /**
     * Extract and parse parameters from construction
     *
     * @param array $construction
     * @param Template $filter
     * @param array $templateVariables
     * @return array
     */
    private function extractParameters(array $construction, Template $filter, array $templateVariables): array
    {
        if (empty($construction['parameters'])) {
            return [];
        }

        /** @var Parameter $tokenizer */
        $tokenizer = $this->parameterTokenizerFactory->create();
        $tokenizer->setString($construction['parameters']);
        $parameters = $tokenizer->tokenize();

        foreach ($parameters as $key => $value) {
            if (substr($value, 0, 1) === '$') {
                $parameters[$key] = $this->variableResolver->resolve(
                    substr($value, 1),
                    $filter,
                    $templateVariables
                );
            }
        }

        return $parameters;
    }
}
