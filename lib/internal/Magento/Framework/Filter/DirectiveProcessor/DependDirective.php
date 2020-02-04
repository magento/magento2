<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Will only render the the content of the directive if the condition is truthy
 */
class DependDirective implements DirectiveProcessorInterface
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

    /**
     * @param VariableResolverInterface $variableResolver
     */
    public function __construct(
        VariableResolverInterface $variableResolver
    ) {
        $this->variableResolver = $variableResolver;
    }

    /**
     * @inheritdoc
     */
    public function process(array $construction, Template $filter, array $templateVariables): string
    {
        if (empty($templateVariables)) {
            // If template processing
            return $construction[0];
        }

        if ($this->variableResolver->resolve($construction[1], $filter, $templateVariables) == '') {
            return '';
        } else {
            return $filter->filter($construction[2]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getRegularExpression(): string
    {
        return Template::CONSTRUCTION_DEPEND_PATTERN;
    }
}
