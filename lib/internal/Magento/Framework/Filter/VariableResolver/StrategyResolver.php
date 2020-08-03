<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\VariableResolver;

use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Responsible for resolving variables based on the mode of the template
 */
class StrategyResolver implements VariableResolverInterface
{
    /**
     * @var LegacyResolver
     */
    private $legacyResolver;

    /**
     * @var StrictResolver
     */
    private $strictResolver;

    /**
     * @param LegacyResolver $legacyResolver
     * @param StrictResolver $strictResolver
     */
    public function __construct(LegacyResolver $legacyResolver, StrictResolver $strictResolver)
    {
        $this->legacyResolver = $legacyResolver;
        $this->strictResolver = $strictResolver;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $value, Template $filter, array $templateVariables)
    {
        if ($filter->isStrictMode()) {
            return $this->strictResolver->resolve($value, $filter, $templateVariables);
        } else {
            return $this->legacyResolver->resolve($value, $filter, $templateVariables);
        }
    }
}
