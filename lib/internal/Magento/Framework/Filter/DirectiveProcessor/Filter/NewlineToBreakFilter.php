<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor\Filter;

use Magento\Framework\Filter\DirectiveProcessor\FilterInterface;

/**
 * Converts newlines to <br>
 */
class NewlineToBreakFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public function filterValue(string $value): string
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
