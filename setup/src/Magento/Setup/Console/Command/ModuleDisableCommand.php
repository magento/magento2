<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

class ModuleDisableCommand extends AbstractModuleCommand
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
     * Enable modules
     *
     * @return bool
     */
    protected function isEnable()
    {
        return false;
    }
}
