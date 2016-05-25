<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Console\Command\AbstractCacheManageCommand;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Object Manager
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(InstallerFactory $installerFactory, ObjectManagerProvider $objectManagerProvider)
    {
        $this->installerFactory = $installerFactory;
        $this->objectManagerProvider = $objectManagerProvider;
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
        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = $this->objectManagerProvider->get();
        $areaCode = 'setup';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
        $configLoader = $objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $objectManager->configure($configLoader->load($areaCode));

        $keepGenerated = $input->getOption(self::INPUT_KEY_KEEP_GENERATED);
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->updateModulesSequence($keepGenerated);
        $installer->installSchema();
        $installer->installDataFixtures();
        if (!$keepGenerated) {
            $output->writeln('<info>Please re-run Magento compile command</info>');
        }

        //TODO: to be removed in scope of MAGETWO-53476
        $writeFactory = $objectManager->get('\Magento\Framework\Filesystem\Directory\WriteFactory');
        $write = $writeFactory->create(BP);
        /** @var \Magento\Framework\App\Filesystem\DirectoryList $dirList */
        $dirList = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');

        $pathToCacheStatus = $write->getRelativePath($dirList->getPath(DirectoryList::VAR_DIR) . '/.cachestates.json');

        if ($write->isExist($pathToCacheStatus)) {
            $params = array_keys(json_decode($write->readFile($pathToCacheStatus), true));
            $command = $this->getApplication()->find('cache:enable');

            $arguments = ['command' => 'cache:enable', AbstractCacheManageCommand::INPUT_KEY_TYPES    => $params ];
            $returnCode = $command->run(new ArrayInput($arguments), $output);

            $write->delete($pathToCacheStatus);
            if (isset($returnCode) && $returnCode > 0) {
                $output->writeln('<error> Error occured during upgrade</error>');
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        }
    }
}
