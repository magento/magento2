<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleSimpleTemplateDirective\Model;

use Magento\Framework\Filter\SimpleDirective\ProcessorInterface;
use Magento\Framework\Filter\Template;

/**
 * Handles the {{mydir}} directive
 */
class MyDirProcessor implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'mydir';
    }

    /**
     * @inheritDoc
     */
    public function process(
        $value,
        array $parameters,
        ?string $html,
        Template $filter,
        array $templateVariables
    ): string {
        return $value . $parameters['param1'] . $html . ($templateVariables['foo'] ?? '');
    }

    /**
     * @inheritDoc
     */
    public function getDefaultFilters(): ?array
    {
        return ['foofilter'];
    }
}
