<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;

/**
 * Extension attributes injectors processor interface
 *
 * @api
 */
interface InjectorProcessorInterface
{
    /**
     * Process object for injections
     *
     * @param string $type
     * @param ExtensibleDataInterface $object
     * @param ExtensionAttributesInterface $extensionAttributes
     */
    public function execute(string $type, $object, $extensionAttributes): void;
}
