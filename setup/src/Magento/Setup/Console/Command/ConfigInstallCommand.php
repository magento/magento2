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
     * @var array
     */
    protected $errors;

    /**
     * Constructor
     *s
     * @param \Magento\Setup\Model\ConfigModel $configModel
     */
    public function __construct(ConfigModel $configModel)
    {
        $this->configModel = $configModel;
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
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
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
