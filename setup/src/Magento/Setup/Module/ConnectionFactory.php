<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Connection adapter factory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConnectionFactory implements \Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $connectionConfig)
    {
        $quote = new \Magento\Framework\DB\Platform\Quote();
        $selectFactory = new \Magento\Framework\DB\SelectFactory(
            new \Magento\Framework\DB\Select\SelectRenderer(
                [
                    'distinct' => [
                            'renderer' => new \Magento\Framework\DB\Select\DistinctRenderer(),
                            'sort' => 100,
                            'part' => 'distinct'
                        ],
                    'columns' => [
                            'renderer' => new \Magento\Framework\DB\Select\ColumnsRenderer($quote),
                            'sort' => 200,
                            'part' => 'columns'
                        ],
                    'union' => [
                            'renderer' => new \Magento\Framework\DB\Select\UnionRenderer(),
                            'sort' => 300,
                            'part' => 'union'
                        ],
                    'from' => [
                            'renderer' => new \Magento\Framework\DB\Select\FromRenderer($quote),
                            'sort' => 400,
                            'part' => 'from'
                        ],
                    'where' => [
                            'renderer' => new \Magento\Framework\DB\Select\WhereRenderer(),
                            'sort' => 500,
                            'part' => 'where'
                        ],
                    'group' => [
                            'renderer' => new \Magento\Framework\DB\Select\GroupRenderer($quote),
                            'sort' => 600,
                            'part' => 'group'
                        ],
                    'having' => [
                            'renderer' => new \Magento\Framework\DB\Select\HavingRenderer(),
                            'sort' => 700,
                            'part' => 'having'
                        ],
                    'order' => [
                            'renderer' => new \Magento\Framework\DB\Select\OrderRenderer($quote),
                            'sort' => 800,
                            'part' => 'order'
                        ],
                    'limit' => [
                            'renderer' => new \Magento\Framework\DB\Select\LimitRenderer(),
                            'sort' => 900,
                            'part' => 'limitcount'
                        ],
                    'for_update' => [
                            'renderer' => new \Magento\Framework\DB\Select\ForUpdateRenderer(),
                            'sort' => 1000,
                            'part' => 'forupdate'
                        ],
                ]
            )
        );
        $objectManagerProvider = $this->serviceLocator->get(\Magento\Setup\Model\ObjectManagerProvider::class);
        $mysqlFactory = new \Magento\Framework\DB\Adapter\Pdo\MysqlFactory($objectManagerProvider->get());
        $resourceInstance = new Mysql($connectionConfig, $mysqlFactory);
        return $resourceInstance->getConnection(
            $this->serviceLocator->get(\Magento\Framework\DB\Logger\Quiet::class),
            $selectFactory
        );
    }
}
