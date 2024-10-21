<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
