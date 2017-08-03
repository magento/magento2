<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;

/**
 * Class \Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache
 *
 * @since 2.0.0
 */
class InterceptionCache implements OperationInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $data = [];

    /**
     * @var \Magento\Framework\Interception\Config\Config
     * @since 2.0.0
     */
    private $configInterface;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions
     * @since 2.0.0
     */
    private $interceptionsInstancesNamesList;

    /**
     * @param \Magento\Framework\Interception\Config\Config $configInterface
     * @param \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions $interceptionsInstancesNamesList
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getName()
    {
        return 'Interception cache generation';
    }
}
