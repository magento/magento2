<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Code\GeneratedFiles;

/**
 * Command for updating installed application after the code base has changed
 */
class UpgradeCommand extends AbstractSetupCommand
{
    /**
     * Option to skip deletion of var/generation directory
     */
    const INPUT_KEY_KEEP_GENERATED = 'keep-generated';

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GeneratedFiles
     */
    private $generatedFiles;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(InstallerFactory $installerFactory, ObjectManagerProvider $objectManagerProvider)
    {
        $this->installerFactory = $installerFactory;
        /** @var \Magento\Framework\ObjectManagerInterface objectManager */
        $this->objectManager = $objectManagerProvider->get();
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_KEEP_GENERATED,
                null,
                InputOption::VALUE_NONE,
                'Prevents generated files from being deleted. ' . PHP_EOL .
                'We discourage using this option except when deploying to production. ' . PHP_EOL .
                'Consult your system integrator or administrator for more information.'
            )
        ];
        $this->setName('setup:upgrade')
            ->setDescription('Upgrades the Magento application, DB data, and schema')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getGeneratedFiles()->requestRegeneration();
        $areaCode = 'setup';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
        $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $this->objectManager->configure($configLoader->load($areaCode));

        $keepGenerated = $input->getOption(self::INPUT_KEY_KEEP_GENERATED);
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->updateModulesSequence($keepGenerated);
        $installer->installSchema();
        $installer->installDataFixtures();
        if (!$keepGenerated) {
            $output->writeln('<info>Please re-run Magento compile command</info>');
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Get deployment config
     *
     * @return GeneratedFiles
     * @deprecated
     */
    private function getGeneratedFiles()
    {
        if (!($this->generatedFiles instanceof GeneratedFiles)) {
            return $this->objectManager->get(GeneratedFiles::class);
        }
        return $this->generatedFiles;
    }
}
