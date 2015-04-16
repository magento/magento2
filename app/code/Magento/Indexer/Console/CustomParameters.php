<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console;

use Magento\Framework\Console\ParameterInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class implementing custom environment variables required for console command.
 */
class CustomParameters implements ParameterInterface
{
    /**
     * Parameters
     *
     * @var array
     */
    private $params;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->params = $_SERVER;
    }

    /**
     * Returns custom parameters for console
     *
     * @return array
     */
    public function getCustomParameters()
    {
        $this->params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $this->params[StoreManager::PARAM_RUN_TYPE] = 'store';
        return $this->params;
    }
}
