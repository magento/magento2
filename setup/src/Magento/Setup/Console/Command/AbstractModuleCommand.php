<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for Enable and Disable commands to consolidate common logic
 */
abstract class AbstractModuleCommand extends AbstractSetupCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_MODULES = 'module';
    const INPUT_KEY_CLEAR_STATIC_CONTENT = 'clear-static-content';

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManager = $objectManagerProvider->get();
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_KEY_MODULES,
            InputArgument::IS_ARRAY | ($this->isModuleRequired() ? InputArgument::REQUIRED : InputArgument::OPTIONAL),
            'Name of the module'
        );
        $this->addOption(
            self::INPUT_KEY_CLEAR_STATIC_CONTENT,
            'c',
            InputOption::VALUE_NONE,
            'Clear generated static view files. Necessary, if the module(s) have static view files'
        );

        parent::configure();
    }

    /**
     * Returns if module argument is required
     *
     * @return bool
     */
    abstract protected function isModuleRequired();

    /**
     * Cleanup after updated modules status
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function cleanup(InputInterface $input, OutputInterface $output)
    {
        /** @var \Magento\Framework\App\Cache $cache */
        $cache = $this->objectManager->get(\Magento\Framework\App\Cache::class);
        $cache->clean();
        $output->writeln('<info>Cache cleared successfully.</info>');
        /** @var \Magento\Framework\App\State\CleanupFiles $cleanupFiles */
        $cleanupFiles = $this->objectManager->get(\Magento\Framework\App\State\CleanupFiles::class);
        $cleanupFiles->clearCodeGeneratedClasses();
        $output->writeln(
            "<info>Generated classes cleared successfully. Please run the 'setup:di:compile' command to "
            . 'generate classes.</info>'
        );
        if ($input->getOption(self::INPUT_KEY_CLEAR_STATIC_CONTENT)) {
            $cleanupFiles->clearMaterializedViewFiles();
            $output->writeln('<info>Generated static view files cleared successfully.</info>');
        } else {
            $output->writeln(
                "<info>Info: Some modules might require static view files to be cleared. To do this, run '"
                . $this->getName() . "' with the --" . self::INPUT_KEY_CLEAR_STATIC_CONTENT
                . ' option to clear them.</info>'
            );
        }
    }
}
