<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;

use Zend\ServiceManager\ServiceManager;

/**
 * Class CommandList contains predefined list of commands for Setup
 */
class CommandList
{
    /**
     * Service Manager
     *
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Constructor
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Gets list of setup command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            'Magento\Setup\Console\Command\Admin\User\CreateCommand',
            'Magento\Setup\Console\Command\I18n\CollectPhrasesCommand',
            'Magento\Setup\Console\Command\I18n\PackCommand',
            'Magento\Setup\Console\Command\Info\AdminUriCommand',
            'Magento\Setup\Console\Command\Info\Backups\ListCommand',
            'Magento\Setup\Console\Command\Info\Currency\ListCommand',
            'Magento\Setup\Console\Command\Info\Language\ListCommand',
            'Magento\Setup\Console\Command\Info\Timezone\ListCommand',
            'Magento\Setup\Console\Command\Maintenance\AllowIpsCommand',
            'Magento\Setup\Console\Command\Maintenance\DisableCommand',
            'Magento\Setup\Console\Command\Maintenance\EnableCommand',
            'Magento\Setup\Console\Command\Maintenance\StatusCommand',
            'Magento\Setup\Console\Command\Module\DisableCommand',
            'Magento\Setup\Console\Command\Module\EnableCommand',
            'Magento\Setup\Console\Command\Module\StatusCommand',
            'Magento\Setup\Console\Command\Module\UninstallCommand',
            'Magento\Setup\Console\Command\Setup\BackupCommand',
            'Magento\Setup\Console\Command\Setup\Config\SetCommand',
            'Magento\Setup\Console\Command\Setup\Cron\RunCommand',
            'Magento\Setup\Console\Command\Setup\Db\StatusCommand',
            'Magento\Setup\Console\Command\Setup\DbData\UpgradeCommand',
            'Magento\Setup\Console\Command\Setup\DbSchema\UpgradeCommand',
            'Magento\Setup\Console\Command\Setup\Dependencies\ShowFrameworkCommand',
            'Magento\Setup\Console\Command\Setup\Dependencies\ShowModulesCircularCommand',
            'Magento\Setup\Console\Command\Setup\Dependencies\ShowModulesCommand',
            'Magento\Setup\Console\Command\Setup\Di\CompileCommand',
            'Magento\Setup\Console\Command\Setup\Di\CompileMultiTenantCommand',
            'Magento\Setup\Console\Command\Setup\InstallCommand',
            'Magento\Setup\Console\Command\Setup\Performance\GenerateFixturesCommand',
            'Magento\Setup\Console\Command\Setup\RollbackCommand',
            'Magento\Setup\Console\Command\Setup\StoreConfiguration\SetCommand',
            'Magento\Setup\Console\Command\Setup\UninstallCommand',
            'Magento\Setup\Console\Command\Setup\UpgradeCommand',
        ];
    }

    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Exception
     */
    public function getCommands()
    {
        $commands = [];

        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->serviceManager->create($class);
            } else {
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
