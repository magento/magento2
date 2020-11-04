<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\SearchStorefrontConfig\Console\Command;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Setup\Model\ConfigGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for grpc server and grpc_services_map initialization
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config extends Command
{
    /**
     * Command name
     * @var string
     */
    private const COMMAND_NAME = 'storefront:search:init';

    /**
     * Configuration for Elasticsearch
     */
    const ELASTICSEARCH_HOST         = 'elastic';
    const ELASTICSEARCH_ENGINE       = 'storefrontElasticsearch6';
    const ELASTICSEARCH_PORT         = '9200';
    const ELASTICSEARCH_INDEX_PREFIX = 'magento2';
    /**
     * Other settings
     */

    /**
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * Installer constructor.
     * @param Writer          $deploymentConfigWriter
     * @param DateTime        $dateTime
     * @param ConfigGenerator $configGenerator
     */
    public function __construct(
        Writer $deploymentConfigWriter,
        DateTime $dateTime,
        ConfigGenerator $configGenerator
    ) {
        parent::__construct();
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->dateTime = $dateTime;
        $this->configGenerator = $configGenerator;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)->setDescription(
            'Adds minimum required config data to env.php'
        );

        parent::configure();
    }

    /**
     * Prepare cache list
     *
     * @return array
     */
    private function getCacheTypes(): array
    {
        return [
            'config'          => 1,
            'compiled_config' => 1
        ];
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $config = [
            'app_env' => [
                'crypt' => [
                    'key' => 'crypt_key'
                ],
                'cache_types' => $this->getCacheTypes(),
                'resource' => [
                    'default_setup' => [
                        'connection' => 'default'
                    ]
                ],
                'system'      => [
                    'stores' => [
                        'catalog' => [
                            'layered_navigation' => [
                                'price_range_calculation' => 'auto',
                                'interval_division_limit' => 1,
                                'price_range_step' => 100,
                                'price_range_max_intervals' => 10,
                                'one_price_interval' => 1
                            ]
                        ]
                    ]
                ],
                'db' => [
                    'connection' => [
                        'default' => [
                            'host' => 'db',
                            'dbname' => 'magento',
                            'username' => 'root',
                            'password' => '',
                            'model' => 'mysql4',
                            'engine' => 'innodb',
                            'initStatements' => 'SET NAMES utf8;',
                            'active' => '1',
                            'driver_options' => [
                                1014 => false
                            ]
                        ]
                    ],
                    'table_prefix' => ''
                ],
                'search-store-front' => [
                    'connections' => [
                        'default' => [
                            'protocol' => 'http',
                            'hostname' => self::ELASTICSEARCH_HOST,
                            'port' => self::ELASTICSEARCH_PORT,
                            'enable_auth' => '',
                            'username' => '',
                            'password' => '',
                            'timeout' => 30
                        ]
                    ],
                    'engine' => self::ELASTICSEARCH_ENGINE,
                    'minimum_should_match' => 1,
                    'index_prefix' => self::ELASTICSEARCH_INDEX_PREFIX,
                    'source_current_version' => 1
                ],
                'install'     => [
                    'date' => $this->dateTime->formatDate(true)
                ]
            ]
        ];

        $config['app_env'] = array_replace_recursive(
            $config['app_env'],
            $this->configGenerator->createCryptConfig([])->getData(),
            $this->configGenerator->createModeConfig()->getData()
        );

        $this->deploymentConfigWriter->saveConfig($config);
    }
}
