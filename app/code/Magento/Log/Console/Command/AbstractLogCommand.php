<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Abstract class for commands related to manage Magento logs
 */
abstract class AbstractLogCommand extends Command
{
    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $this->objectManager = $objectManagerFactory->create($params);
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
