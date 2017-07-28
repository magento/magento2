<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

/**
 * Command for disabling list or all of modules
 * @since 2.0.0
 */
class ModuleDisableCommand extends AbstractModuleManageCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function isEnable()
    {
        return false;
    }
}
