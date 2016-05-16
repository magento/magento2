<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $repositories = $this->serviceDataAttributesScanner->collectEntities($files);
        foreach ($repositories as $entityName) {
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
