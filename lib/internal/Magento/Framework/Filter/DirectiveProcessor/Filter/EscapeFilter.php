<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor\Filter;

use Magento\Framework\Escaper;
use Magento\Framework\Filter\DirectiveProcessor\FilterInterface;

/**
 * EscapesInput
 */
class EscapeFilter implements FilterInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * @inheritDoc
     */
    public function filterValue(string $value, array $params): string
    {
        $type = $params[0] ?? 'html';

        switch ($type) {
            case 'html':
                return $this->escaper->escapeHtml($value);

            case 'htmlentities':
                return htmlentities($value, ENT_QUOTES);

            case 'url':
                return rawurlencode($value);
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'escape';
    }
}
