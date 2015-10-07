<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var Scanner\DirectoryScanner
     */
    private $directoryScanner;

    /**
     * @var Scanner\ServiceDataAttributesScanner
     */
    private $serviceDataAttributesScanner;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Scanner\DirectoryScanner $directoryScanner
     * @param Scanner\ServiceDataAttributesScanner $repositoryScanner
     * @param array $data
     */
    public function __construct(
        Scanner\DirectoryScanner $directoryScanner,
        Scanner\ServiceDataAttributesScanner $repositoryScanner,
        $data = []
    ) {
        $this->directoryScanner = $directoryScanner;
        $this->serviceDataAttributesScanner = $repositoryScanner;
        $this->data = $data;
    }

    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation()
    {
        if (array_diff(array_keys($this->data), ['filePatterns', 'paths'])
            !== array_diff(['filePatterns', 'paths'], array_keys($this->data))) {
            return;
        }
        $files = [];
        foreach ($this->data['paths'] as $path) {
            $files = array_merge_recursive($files, $this->directoryScanner->scan($path, $this->data['filePatterns']));
        }
        $repositories = $this->serviceDataAttributesScanner->collectEntities($files['extension_attributes']);
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
