<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\Template\Tokenizer\ParameterFactory;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Allows templates to be included inside other templates
 *
 * Usage:
 *
 *     {{template config_path="<PATH>"}}
 *
 * <PATH> equals the XPATH to the system configuration value that contains the value of the template.
 * This directive is useful to include things like a global header/footer.
 */
class TemplateDirective implements DirectiveProcessorInterface
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

    /**
     * @var ParameterFactory
     */
    private $parameterFactory;

    /**
     * @param VariableResolverInterface $variableResolver
     * @param ParameterFactory $parameterFactory
     */
    public function __construct(
        VariableResolverInterface $variableResolver,
        ParameterFactory $parameterFactory
    ) {
        $this->variableResolver = $variableResolver;
        $this->parameterFactory = $parameterFactory;
    }

    /**
     * @inheritdoc
     */
    public function process(array $construction, Template $filter, array $templateVariables): string
    {
        // Processing of {template config_path=... [...]} statement
        $templateParameters = $this->getParameters($construction[2], $filter, $templateVariables);
        if (!isset($templateParameters['config_path']) || !$filter->getTemplateProcessor()) {
            // Not specified template or not set include processor
            $replacedValue = '{Error in template processing}';
        } else {
            // Including of template
            $configPath = $templateParameters['config_path'];
            unset($templateParameters['config_path']);
            $templateParameters = array_merge_recursive($templateParameters, $templateVariables);
            $replacedValue = call_user_func($filter->getTemplateProcessor(), $configPath, $templateParameters);
        }

        return $replacedValue;
    }

    /**
     * Return associative array of parameters.
     *
     * @param string $value raw parameters
     * @param Template $filter
     * @param array $templateVariables
     * @return array
     */
    private function getParameters($value, Template $filter, array $templateVariables): array
    {
        $tokenizer = new Template\Tokenizer\Parameter();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();
        foreach ($params as $key => $value) {
            if (substr($value, 0, 1) === '$') {
                $params[$key] = $this->variableResolver->resolve(substr($value, 1), $filter, $templateVariables);
            }
        }

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function getRegularExpression(): string
    {
        return Template::CONSTRUCTION_TEMPLATE_PATTERN;
    }
}
