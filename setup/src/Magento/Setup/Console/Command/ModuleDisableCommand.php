<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Console\Command;

/**
 * Command for disabling list or all of modules
 */
class ModuleDisableCommand extends AbstractModuleManageCommand
{
    public const NAME = 'module:disable';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Disables specified modules');
        parent::configure();
    }

    /**
     * Disable modules
     *
     * @return bool
     */
    protected function isEnable()
    {
        return false;
    }
}
