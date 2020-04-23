<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\SimpleDirective;

use Magento\Framework\Filter\Template;

/**
 * An easier mechanism to implement custom directives rather than parsing the whole directive manually
 */
interface ProcessorInterface
{
    /**
     * Unique name of this directive.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Process values given to the directory and return rendered result.
     *
     * @param mixed $value Template var, scalar or null if nothing has been passed to the directive.
     * @param string[] $parameters Additional parameters.
     * @param string|null $html HTML inside the directive.
     * @return string
     */
    public function process(
        $value,
        array $parameters,
        ?string $html
    ): string;

    /**
     * Default filters to apply if none provided in a template.
     *
     * @return string[]|null
     */
    public function getDefaultFilters(): ?array;
}
