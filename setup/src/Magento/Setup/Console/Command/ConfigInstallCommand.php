<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\ConfigModel;
use Magento\Framework\Module\ModuleList;

class ConfigInstallCommand extends Command
{
    /**
     * @var ConfigModel
     */
    protected $configModel;

    /**
     * @var ConfigFilePool
     */
    protected $configFilePool;

    /**
     * Enabled module list
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * Constructor
     *
     * @param \Magento\Setup\Model\ConfigModel $configModel
     * @param ModuleList $moduleList
     */
    public function __construct(ConfigModel $configModel, ModuleList $moduleList)
    {
        $this->configModel = $configModel;
        $this->moduleList = $moduleList;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = $this->configModel->getAvailableOptions();

        $this
            ->setName('config:install')
            ->setDescription('Install deployment configuration')
            ->setDefinition($options);

        $this->ignoreValidationErrors();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configModel->process($input->getOptions());
        $output->writeln('<info>Deployment config has been saved.</info>');
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$this->moduleList->isModuleInfoAvailable()) {
            $output->writeln(
                '<info>There is no module configuration available, so all modules are enabled.</info>'
            );
        }

        $inputOptions = $input->getOptions();

        $errors = $this->configModel->validate($inputOptions);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>$error</error>");
            }
            exit(1);
        }
    }
}
