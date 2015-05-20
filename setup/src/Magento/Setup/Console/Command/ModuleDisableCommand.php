<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

/**
 * Command for disabling list or all of modules
 */
class ModuleDisableCommand extends AbstractModuleManageCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('module:disable')
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
