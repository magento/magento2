<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use \Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;

/**
 * Class \Magento\Setup\Console\Command\InfoAdminUriCommand
 *
 * @since 2.0.0
 */
class InfoAdminUriCommand extends Command
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     * @since 2.0.0
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('info:adminuri')
            ->setDescription('Displays the Magento Admin URI');
        parent::configure();
    }

    /**
      * {@inheritdoc}
     * @since 2.0.0
      */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            "\nAdmin URI: /"
            . $this->deploymentConfig->get(BackendConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME)
            . "\n"
        );
    }
}
