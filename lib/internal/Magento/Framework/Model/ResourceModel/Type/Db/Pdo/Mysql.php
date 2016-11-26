<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Type\Db\Pdo;

use Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface;
use Magento\Framework\DB;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\Stdlib;

// @codingStandardsIgnoreStart
class Mysql extends \Magento\Framework\Model\ResourceModel\Type\Db implements ConnectionAdapterInterface
{
    // @codingStandardsIgnoreEnd

    /**
     * @var Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $connectionConfig;

    /**
     * @var SelectFactory
     */
    protected $selectFactory;

    /**
     * @param Stdlib\StringUtils $string
     * @param Stdlib\DateTime $dateTime
     * @param SelectFactory $selectFactory
     * @param array $config
     */
    public function __construct(
        Stdlib\StringUtils $string,
        Stdlib\DateTime $dateTime,
        SelectFactory $selectFactory,
        array $config
    ) {
        $this->string = $string;
        $this->dateTime = $dateTime;
        $this->selectFactory = $selectFactory;
        $this->connectionConfig = $this->getValidConfig($config);

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(DB\LoggerInterface $logger)
    {
        $connection = $this->getDbConnectionInstance($logger);

        $profiler = $connection->getProfiler();
        if ($profiler instanceof DB\Profiler) {
            $profiler->setType($this->connectionConfig['type']);
            $profiler->setHost($this->connectionConfig['host']);
        }

        return $connection;
    }

    /**
     * Create and return DB connection object instance
     *
     * @param DB\LoggerInterface $logger
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected function getDbConnectionInstance(DB\LoggerInterface $logger)
    {
        $className = $this->getDbConnectionClassName();
        return new $className($this->string, $this->dateTime, $logger, $this->selectFactory, $this->connectionConfig);
    }

    /**
     * Retrieve DB connection class name
     *
     * @return string
     */
    protected function getDbConnectionClassName()
    {
        return DB\Adapter\Pdo\Mysql::class;
    }

    /**
     * Validates the config and adds default options, if any is missing
     *
     * @param array $config
     * @return array
     */
    private function getValidConfig(array $config)
    {
        $default = ['initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false];
        foreach ($default as $key => $value) {
            if (!isset($config[$key])) {
                $config[$key] = $value;
            }
        }
        $required = ['host'];
        foreach ($required as $name) {
            if (!isset($config[$name])) {
                throw new \InvalidArgumentException("MySQL adapter: Missing required configuration option '$name'");
            }
        }

        if (isset($config['port'])) {
            throw new \InvalidArgumentException(
                "Port must be configured within host (like '$config[host]:$config[port]') parameter, not within port"
            );
        }

        $config['active'] = !(
            $config['active'] === 'false'
            || $config['active'] === false
            || $config['active'] === '0'
        );

        return $config;
    }
}
