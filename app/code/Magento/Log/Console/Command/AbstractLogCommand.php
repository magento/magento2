<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\Bootstrap;
use Magento\Store\Model\StoreManager;
use Magento\Log\Model\Shell\Command\Factory;
use Magento\Framework\App\DeploymentConfig;

/**
 * Abstract class for commands related to manage Magento logs
 */
abstract class AbstractLogCommand extends Command
{
    /**
     * Command factory
     *
     * @var Factory
     */
    protected $commandFactory;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $bootstrap = Bootstrap::create(BP, $params);
        $this->commandFactory = new Factory($bootstrap->getObjectManager());
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
    }
}
