<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\Filter\DirectiveProcessor\Filter\FilterApplier;
use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Resolves var directives
 */
class VarDirective implements DirectiveProcessorInterface
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

    /**
     * @var FilterApplier
     */
    private $filterApplier;

    /**
     * @param VariableResolverInterface $variableResolver
     * @param FilterApplier $filterApplier
     */
    public function __construct(
        VariableResolverInterface $variableResolver,
        FilterApplier $filterApplier
    ) {
        $this->variableResolver = $variableResolver;
        $this->filterApplier = $filterApplier;
    }

    /**
     * @inheritdoc
     */
    public function process(array $construction, Template $filter, array $templateVariables): string
    {
        if (empty($construction[2])) {
            return $construction[0];
        }

        $result = (string)$this->variableResolver->resolve($construction[2], $filter, $templateVariables);

        if (isset($construction['filters']) && strpos($construction['filters'], '|') !== false) {
            $result = $this->filterApplier->applyFromRawParam($construction['filters'], $result);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getRegularExpression(): string
    {
        return '/{{(var)(.*?)(?P<filters>(?:\|[a-z0-9:_-]+)+)?}}/si';
    }
}
