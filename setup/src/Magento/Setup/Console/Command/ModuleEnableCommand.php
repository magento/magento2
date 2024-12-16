<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Console\Command;

/**
 * Command for enabling list or all of modules
 */
class ModuleEnableCommand extends AbstractModuleManageCommand
{
    public const NAME = 'module:enable';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Enables specified modules');
        parent::configure();
    }

    /**
     * Enable modules
     *
     * @return bool
     */
    protected function isEnable()
    {
        return true;
    }
}
