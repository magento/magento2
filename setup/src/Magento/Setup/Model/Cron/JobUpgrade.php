<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\App\Cache;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class JobUpgrade extends AbstractJob
{
    private $cache;

    private $maintenanceMode;

    private $cleanupFiles;

    public function __construct(
        AbstractSetupCommand $command,
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->cleanupFiles = $objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $this->cache = $objectManager->get('Magento\Framework\App\Cache');
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct($command, $output, $status, $name, $params);
    }

    public function execute()
    {
        try {
            $this->params['command'] = 'setup:upgrade';
            $this->command->run(new ArrayInput($this->params), $this->output);
            $this->status->add('Cleaning generated files...');
            $this->cleanupFiles->clearCodeGeneratedFiles();
            $this->status->add('Clearing cache...');
            $this->cache->clean();
            $this->status->add('Disabling maintenance mode...');
            $this->maintenanceMode->set(false);
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }
}