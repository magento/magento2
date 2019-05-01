<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute;

use LogicException;
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
     * @param array $data
     * @return array
     */
    public function execute(string $type, array $data): array
    {
        $config = $this->injectorConfig->get();

        $data['extension_attributes'] = $data['extension_attributes'] ?? [];

        if (isset($config[$type])) {
            foreach ($config[$type] as $injectorClassName) {
                /** @var InjectorProcessorInterface $injector */
                $injector = $this->objectManager->get($injectorClassName);
                if (!($injector instanceof InjectorProcessorInterface)) {
                    throw new LogicException(
                        $config[$type] . ' injector class must implement ' . InjectorProcessorInterface::class
                    );
                }
                $data['extension_attributes'] = array_replace(
                    $data['extension_attributes'],
                    $injector->execute($type, $data)
                );
            }
        }

        return $data['extension_attributes'];
    }
}
