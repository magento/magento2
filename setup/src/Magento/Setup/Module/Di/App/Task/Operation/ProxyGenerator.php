<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;

class ProxyGenerator implements OperationInterface
{
    /**
     * @var Scanner\DirectoryScanner
     */
    private $directoryScanner;

    /**
     * @var Scanner\XmlScanner
     */
    private $proxyScanner;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Scanner\DirectoryScanner $directoryScanner
     * @param Scanner\XmlScanner $proxyScanner
     * @param array $data
     */
    public function __construct(
        Scanner\DirectoryScanner $directoryScanner,
        Scanner\XmlScanner $proxyScanner,
        $data = []
    ) {
        $this->directoryScanner = $directoryScanner;
        $this->proxyScanner = $proxyScanner;
        $this->data = $data;
    }

    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation()
    {
        if (array_diff(array_keys($this->data), ['filePatterns', 'paths', 'excludePatterns'])
            !== array_diff(['filePatterns', 'paths', 'excludePatterns'], array_keys($this->data))) {
            return;
        }

        $files = [];
        foreach ($this->data['paths'] as $path) {
            $files = array_merge_recursive(
                $files,
                $this->directoryScanner->scan($path, $this->data['filePatterns'], $this->data['excludePatterns'])
            );
        }
        $proxies = $this->proxyScanner->collectEntities($files['di']);
        foreach ($proxies as $entityName) {
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
        return 'Proxies code generation';
    }
}
