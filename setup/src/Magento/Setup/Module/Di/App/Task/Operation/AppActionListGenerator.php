<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;

/**
 * Pregenerates actions for Magento
 */
class AppActionListGenerator implements OperationInterface
{
    /**
     * @var ModuleReader
     */
    private $moduleReader;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigWriterInterface
     */
    private $configWriter;

    /**
     * @param ModuleReader $moduleReader
     * @param ConfigWriterInterface $configWriter
     */
    public function __construct(
        ModuleReader $moduleReader,
        ConfigWriterInterface $configWriter
    ) {
        $this->moduleReader = $moduleReader;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritDoc
     */
    public function doOperation()
    {
        $actionList = $this->moduleReader->getActionFiles();
        $this->configWriter->write(
            'app_action_list',
            $actionList
        );
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'App action list generation';
    }
}
