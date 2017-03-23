<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;

class ProxyGenerator implements OperationInterface
{
    /**
     * @var Scanner\XmlScanner
     */
    private $proxyScanner;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Scanner\ConfigurationScanner
     */
    private $configurationScanner;

    /**
     * @param Scanner\XmlScanner $proxyScanner
     * @param Scanner\ConfigurationScanner $configurationScanner
     * @param array $data
     */
    public function __construct(
        Scanner\XmlScanner $proxyScanner,
        \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner $configurationScanner,
        $data = []
    ) {
        $this->proxyScanner = $proxyScanner;
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
        $files = $this->configurationScanner->scan('di.xml');
        $proxies = $this->proxyScanner->collectEntities($files);
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
