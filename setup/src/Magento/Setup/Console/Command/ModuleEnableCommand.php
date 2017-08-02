<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

/**
 * Command for enabling list or all of modules
 * @since 2.0.0
 */
class ModuleEnableCommand extends AbstractModuleManageCommand
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('module:enable')
            ->setDescription('Enables specified modules');
        parent::configure();
    }

    /**
     * Enable modules
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isEnable()
    {
        return true;
    }
}
