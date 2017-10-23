<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;

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
     * @var \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions
     */
    private $interceptionsInstancesNamesList;

    /**
     * @param \Magento\Framework\Interception\Config\Config $configInterface
     * @param \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions $interceptionsInstancesNamesList
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Interception\Config\Config $configInterface,
        \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions $interceptionsInstancesNamesList,
        array $data = []
    ) {
        $this->configInterface = $configInterface;
        $this->interceptionsInstancesNamesList = $interceptionsInstancesNamesList;
        $this->data = $data;
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
        foreach ($this->data as $paths) {
            if (!is_array($paths)) {
                $paths = (array)$paths;
            }
            foreach ($paths as $path) {
                $definitions = array_merge($definitions, $this->interceptionsInstancesNamesList->getList($path));
            }
        }

        $this->configInterface->initialize($definitions);
    }

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName()
    {
        return 'Interception cache generation';
    }
}
