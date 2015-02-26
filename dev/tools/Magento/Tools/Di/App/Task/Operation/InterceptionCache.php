<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;

class InterceptionCache implements OperationInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var \Magento\Framework\Interception\Config\Config
     */
    private $configInterface;

    /**
     * @var \Magento\Tools\Di\Code\Reader\InstancesNamesList\Interceptions
     */
    private $interceptionsInstancesNamesList;

    /**
     * @param \Magento\Framework\Interception\Config\Config                  $configInterface
     * @param \Magento\Tools\Di\Code\Reader\ClassesScanner                   $classesScanner
     * @param \Magento\Tools\Di\Code\Reader\InstancesNamesList\Interceptions $interceptionsInstancesNamesList
     * @param array                                                           $data
     */
    public function __construct(
        \Magento\Framework\Interception\Config\Config $configInterface,
        \Magento\Tools\Di\Code\Reader\ClassesScanner $classesScanner,
        \Magento\Tools\Di\Code\Reader\InstancesNamesList\Interceptions $interceptionsInstancesNamesList,
        array $data = []
    ) {
        $this->data = $data;
        $this->configInterface = $configInterface;
        $this->classesScanner = $classesScanner;
        $this->interceptionsInstancesNamesList = $interceptionsInstancesNamesList;
    }

    /**
     * Flushes interception cached configuration and generates a new one
     *
     * @return void
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $definitions = [];
        foreach ($this->data as $path) {
            if (is_readable($path)) {
                array_merge($definitions, $this->interceptionsInstancesNamesList->getList($path));
            }
        }

        $this->configInterface->initialize($definitions);
    }
}
