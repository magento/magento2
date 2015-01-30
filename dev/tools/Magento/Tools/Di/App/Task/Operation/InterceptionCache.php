<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Tools\Di\Code\Reader\ClassesScanner;
use Magento\Tools\Di\Code\Reader\InstancesNamesList;

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

    private $interceptionsInstancesNamesList;

    /**
     * @param \Magento\Framework\Interception\Config\Config $configInterface
     * @param ClassesScanner                                $classesScanner
     * @param InstancesNamesList\Interceptions              $interceptionsInstancesNamesList
     * @param array                                         $data
     */
    public function __construct(
        \Magento\Framework\Interception\Config\Config $configInterface,
        ClassesScanner $classesScanner,
        InstancesNamesList\Interceptions $interceptionsInstancesNamesList,
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
