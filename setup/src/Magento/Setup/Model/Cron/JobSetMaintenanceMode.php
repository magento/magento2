<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Console\Command\AbstractSetupCommand;
use Magento\Setup\Console\Command\MaintenanceDisableCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Job that handles maintenance mode. E.g. "maintenance:enable", "maintenance:disable"
 */
class JobSetMaintenanceMode extends AbstractJob
{
    /**
     * Constructor
     *
     * @param AbstractSetupCommand $command
     * @param ObjectManagerProvider $objectManagerProvider
     * @param OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        AbstractSetupCommand $command,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $this->command = $command;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
    }

    /**
     * Execute job
     *
     * @throws \RuntimeException
     * @return void
     */
    public function execute()
    {
        if ($this->command instanceof MaintenanceDisableCommand && $this->command->isSetAddressInfo()) {
            // Maintenance mode should not be unset from updater application if it was set manually by the admin
            throw new \RuntimeException(
                $this->getExceptionMessage(
                    'Magento maintenance mode was not disabled. It can be disabled from the Magento Backend.'
                )
            );
        }

        try {
            // Prepare the arguments to invoke Symfony run()
            $arguments['command'] = $this->getCommand();
            $this->command->run(new ArrayInput($arguments), $this->output);
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(
                $this->getExceptionMessage($e->getMessage())
            );
        }
    }

    /**
     * Get exception message
     *
     * @param string $msg
     * @return string
     */
    private function getExceptionMessage($msg)
    {
        return sprintf('Could not complete %s successfully: %s', $this, $msg);
    }

    /**
     * Get the command to be run through bin/magento
     *
     * @return string
     */
    private function getCommand()
    {
        return $this->getName() === 'setup:maintenance:enable' ? 'maintenance:enable' : 'maintenance:disable';
    }
}
