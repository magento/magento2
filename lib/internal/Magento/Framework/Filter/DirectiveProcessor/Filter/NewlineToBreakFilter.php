<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor\Filter;

use Magento\Framework\Filter\DirectiveProcessor\FilterInterface;

/**
 * Inserts HTML line breaks before all newlines in a string
 */
class NewlineToBreakFilter implements FilterInterface
{
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filterValue(string $value, array $params): string
    {
        return nl2br($value);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'nl2br';
    }
}
