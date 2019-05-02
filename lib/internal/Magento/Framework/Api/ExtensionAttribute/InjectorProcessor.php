<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute;

use LogicException;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Extension attributes injectors processor
 */
class InjectorProcessor
{
    /**
     * @var InjectorConfig
     */
    private $injectorConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param InjectorConfig $injectorConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        InjectorConfig $injectorConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->injectorConfig = $injectorConfig;
        $this->objectManager = $objectManager;
    }

    /**
     * Process object for injections
     *
     * @param string $type
     * @param ExtensibleDataInterface $object
     * @param ExtensionAttributesInterface $extensionAttributes
     * @return void
     */
    public function execute(string $type, ExtensibleDataInterface $object, $extensionAttributes): void
    {
        $config = $this->injectorConfig->get();

        if (!isset($config[$type])) {
            return;
        }

        foreach ($config[$type] as $injectorClassName) {
            /** @var InjectorProcessorInterface $injector */
            $injector = $this->objectManager->get($injectorClassName);
            if (!($injector instanceof InjectorProcessorInterface)) {
                throw new LogicException(
                    $config[$type] . ' injector class must implement ' . InjectorProcessorInterface::class
                );
            }

            $injector->execute($type, $object, $extensionAttributes);
        }
    }
}
