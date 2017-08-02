<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;

/**
 * Class \Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator
 *
 * @since 2.0.0
 */
class ProxyGenerator implements OperationInterface
{
    /**
     * @var Scanner\XmlScanner
     * @since 2.0.0
     */
    private $proxyScanner;

    /**
     * @var array
     * @since 2.0.0
     */
    private $data;

    /**
     * @var Scanner\ConfigurationScanner
     * @since 2.1.0
     */
    private $configurationScanner;

    /**
     * @param Scanner\XmlScanner $proxyScanner
     * @param Scanner\ConfigurationScanner $configurationScanner
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getName()
    {
        return 'Proxies code generation';
    }
}
