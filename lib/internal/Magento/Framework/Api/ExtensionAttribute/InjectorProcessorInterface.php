<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute;

/**
 * Extension attributes injectors processor interface
  */
interface InjectorProcessorInterface
{
    /**
     * Process object for injections
     *
     * @param string $type
     * @param array $objectData
     * @return array
     */
    public function execute(string $type, array $objectData): array;
}
