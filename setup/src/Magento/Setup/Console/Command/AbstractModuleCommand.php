<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

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
    const INPUT_KEY_ALL = 'all';
    const INPUT_KEY_FORCE = 'force';
    const INPUT_KEY_CLEAR_STATIC_CONTENT = 'clear-static-content';

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition([
                new InputArgument(
                    self::INPUT_KEY_MODULES,
                    InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                    'Name of the module'
                ),
                new InputOption(
                    self::INPUT_KEY_CLEAR_STATIC_CONTENT,
                    'c',
                    InputOption::VALUE_NONE,
                    'Clear generated static view files. Necessary, if the module(s) have static view files'
                ),
                new InputOption(
                    self::INPUT_KEY_FORCE,
                    'f',
                    InputOption::VALUE_NONE,
                    'Bypass dependencies check'
                ),
                new InputOption(
                    self::INPUT_KEY_ALL,
                    null,
                    InputOption::VALUE_NONE,
                    ($this->isEnable() ? 'Enable' : 'Disable') . ' all modules'
                ),
            ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isEnable = $this->isEnable();
        if ($input->getOption(self::INPUT_KEY_ALL)) {
            /** @var \Magento\Framework\Module\FullModuleList $fullModulesList */
            $fullModulesList = $this->objectManagerProvider->get()->get('Magento\Framework\Module\FullModuleList');
            $modules = $fullModulesList->getNames();
        } else {
            $modules = $input->getArgument(self::INPUT_KEY_MODULES);
        }
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln(implode(PHP_EOL, $messages));
            return;
        }
        /**
         * @var \Magento\Framework\Module\Status $status
         */
        $status = $this->objectManagerProvider->get()->get('Magento\Framework\Module\Status');
        try {
            $modulesToChange = $status->getModulesToChange($isEnable, $modules);
        } catch (\LogicException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
        if (!empty($modulesToChange)) {
            $force = $input->getOption(self::INPUT_KEY_FORCE);
            if (!$force) {
                $constraints = $status->checkConstraints($isEnable, $modulesToChange);
                if ($constraints) {
                    $output->writeln(
                        "<error>Unable to change status of modules because of the following constraints:</error>"
                    );
                    $output->writeln('<error>' . implode("\n", $constraints) . '</error>');
                    return;
                }
            }
            $status->setIsEnabled($isEnable, $modulesToChange);
            if ($isEnable) {
                $output->writeln('<info>The following modules have been enabled:</info>');
                $output->writeln('<info>- ' . implode("\n- ", $modulesToChange) . '</info>');
                $output->writeln('');
                $output->writeln(
                    '<info>To make sure the modules are properly enabled,'
                    . " run 'setup:upgrade'.</info>"
                );
            } else {
                $output->writeln('<info>The following modules have been disabled:</info>');
                $output->writeln('<info>- ' . implode("\n- ", $modulesToChange) . '</info>');
                $output->writeln('');
            }
            $this->cleanup($input, $output);
            if ($force) {
                $output->writeln(
                    '<error>Alert: You used the --force option.'
                    . ' As a result, modules might not function properly.</error>'
                );
            }
        } else {
            $output->writeln('<info>No modules were changed.</info>');
        }
    }

    /**
     * Validate list of modules and return error messages
     *
     * @param string[] $modules
     * @return string[]
     */
    protected function validate(array $modules)
    {
        $messages = [];
        if (empty($modules)) {
            $messages[] = '<error>No modules specified. Specify a space-separated list of modules' .
                ' or use the --all option</error>';
        }
        return $messages;
    }

    /**
     * Is it "enable" or "disable" command
     *
     * @return bool
     */
    abstract protected function isEnable();

    /**
     * Cleanup after updated modules status
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function cleanup(InputInterface $input, OutputInterface $output)
    {
        $objectManager = $this->objectManagerProvider->get();
        /** @var \Magento\Framework\App\Cache $cache */
        $cache = $objectManager->get('Magento\Framework\App\Cache');
        $cache->clean();
        $output->writeln('<info>Cache cleared successfully.</info>');
        /** @var \Magento\Framework\App\State\CleanupFiles $cleanupFiles */
        $cleanupFiles = $objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $cleanupFiles->clearCodeGeneratedClasses();
        $output->writeln('<info>Generated classes cleared successfully.</info>');
        if ($input->getOption(self::INPUT_KEY_CLEAR_STATIC_CONTENT)) {
            $cleanupFiles->clearMaterializedViewFiles();
            $output->writeln('<info>Generated static view files cleared successfully.</info>');
        } else {
            $output->writeln(
                '<error>Alert: Generated static view files were not cleared.'
                . ' You can clear them using the --' . self::INPUT_KEY_CLEAR_STATIC_CONTENT . ' option.'
                . ' Failure to clear static view files might cause display issues in the Admin and storefront.</error>'
            );
        }
    }
}
