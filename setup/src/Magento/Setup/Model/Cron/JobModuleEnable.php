<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\App\Cache;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Job that handles module enable command
 */
class JobModuleEnable extends AbstractJob
{
    /**
     * @var \Magento\Framework\App\Cache
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles
     */
    private $cleanupFiles;

    /**
     * @var Magento\Framework\Module\PackageInfoFactory
     */
    private $packageInfoFactory;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Constructor
     *
     * @param AbstractSetupCommand $command
     * @param ObjectManagerProvider $objectManagerProvider
     * @param OutputInterface $output
     * @param PackageInfoFactory $packageInfoFactory
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        AbstractSetupCommand $command,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        PackageInfoFactory $packageInfoFactory,
        Status $status,
        $name,
        $params = []
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->cleanupFiles = $objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $this->cache = $objectManager->get('Magento\Framework\App\Cache');
        $this->command = $command;
        $this->output = $output;
        $this->status = $status;
        $this->packageInfoFactory = $packageInfoFactory;
        parent::__construct($output, $status, $name, $params);
    }

    /**
     * Execute job
     *
     * @throws \RuntimeException
     * @return void
     */
    public function execute()
    {
        try {
            //convert composer package names to internal magento module name
            $packageInfo = $this->packageInfoFactory->create();
            $packages = [];
            foreach($this->params['components'] as $compObj) {
                if(isset($compObj['name']) && (!empty($compObj['name']))) {
                    $moduleNames[] = $packageInfo->getModuleName($compObj['name']);
                }
                else {
                    throw new \RuntimeException('component name is not set.');
                }
            }

            //prepare the arguments to invoke Symfony run()
            $arguments['command'] = 'module:enable';
            $arguments['module'] = $moduleNames;

            $this->command->run(new ArrayInput($arguments), $this->output);

            //perform the generated file cleanup
            $this->status->add('Cleaning generated files...');
            $this->cleanupFiles->clearCodeGeneratedFiles();
            $this->status->add('complete!');

            //perform the cache cleanup
            $this->status->add('Clearing cache...');
            $this->cache->clean();
            $this->status->add('complete!');

        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }
}
