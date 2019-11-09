<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter;

/**
 * Responsible for obtaining the value of variables defined in the template
 */
interface VariableResolverInterface
{
    /**
     * Resolve a template variable's value based on some raw input
     *
     * @param string $value e.g. customer.name or $this.foo() or address.format('html')
     * @param Template $filter
     * @param array $templateVariables The dataset that is available as variables to the template
     * @return string|array|null Should be null if the input cannot be resolved
     */
    public function resolve(string $value, Template $filter, array $templateVariables);
}
