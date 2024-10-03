<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

/**
 * Transforms the output of a directive processor
 *
 * @api
 */
interface FilterInterface
{
    /**
     * Transform or manipulate value
     *
     * @param string $value
     * @param string[] $params
     * @return string
     */
    public function filterValue(string $value, array $params): string;

    /**
     * This filter's unique name.
     *
     * @return string
     */
    public function getName(): string;
}
