<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;

/**
 * Class ServiceDataAttributesGenerator
 *
 * Generates extension classes for data objects.
 */
class ServiceDataAttributesGenerator implements OperationInterface
{
    /**
     * @var Scanner\ServiceDataAttributesScanner
     */
    private $serviceDataAttributesScanner;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Scanner\ConfigurationScanner
     */
    private $configurationScanner;

    /**
     * @param Scanner\ServiceDataAttributesScanner $serviceDataAttributesScanner
     * @param Scanner\ConfigurationScanner $configurationScanner
     * @param array $data
     */
    public function __construct(
        Scanner\ServiceDataAttributesScanner $serviceDataAttributesScanner,
        \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner $configurationScanner,
        $data = []
    ) {
        $this->serviceDataAttributesScanner = $serviceDataAttributesScanner;
        $this->data = $data;
        $this->configurationScanner = $configurationScanner;
    }

    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation()
    {
        $files = $this->configurationScanner->scan('extension_attributes.xml');
        $entities = $this->serviceDataAttributesScanner->collectEntities($files);
        foreach ($entities as $entityName) {
            class_exists($entityName);
        }
    }

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName()
    {
        return 'Service data attributes generation';
    }
}
