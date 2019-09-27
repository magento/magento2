<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Injector;

use Magento\Framework\Api\ExtensionAttribute\InjectorProcessorInterface;

/**
 * Attributes extensions test class
 */
class TestInjector implements InjectorProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function execute(string $type, array $objectData): array
    {
        return [
            'attribute1' => 'value1'
        ];
    }
}
