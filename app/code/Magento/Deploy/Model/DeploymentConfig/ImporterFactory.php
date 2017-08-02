<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for importers.
 *
 * Creates object instance that implements Magento\Framework\App\DeploymentConfig\ImporterInterface interface.
 * @since 2.2.0
 */
class ImporterFactory
{
    /**
     * Magento object manager.
     *
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager the magento object manager
     * @since 2.2.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates object instance by class name.
     *
     * @param string $className the name of class for creation of its object instance
     * @param array $data the array with some additional configuration data for creation of object instance
     * @return ImporterInterface the created object instance
     * @throws \InvalidArgumentException is thrown when object instance does not implement ImporterInterface
     * @since 2.2.0
     */
    public function create($className, array $data = [])
    {
        $importer = $this->objectManager->create($className, $data);

        if (!$importer instanceof ImporterInterface) {
            throw new \InvalidArgumentException(
                'Type "' . $className . '" is not instance of ' . ImporterInterface::class
            );
        }

        return $importer;
    }
}
