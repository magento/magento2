<?php
/**
 * Connection adapter factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql;
use Magento\Framework\Stdlib;
use Zend\ServiceManager\ServiceLocatorInterface;

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
        $resourceInstance = new Mysql(new Stdlib\StringUtils(), new Stdlib\DateTime(), $connectionConfig);

        return $resourceInstance->getConnection($this->serviceLocator->get(\Magento\Framework\DB\Logger\Quiet::class));
    }
}
