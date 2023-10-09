<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
