<?php
/**
 * Connection adapter factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Zend\ServiceManager\ServiceLocatorInterface;

class ConnectionFactory implements \Magento\Framework\Model\Resource\Type\Db\ConnectionFactoryInterface
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
        if (!$connectionConfig || !isset($connectionConfig['active']) || !$connectionConfig['active']) {
            return null;
        }

        $adapterInstance = new \Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql(
            new \Magento\Framework\Stdlib\String(),
            new \Magento\Framework\Stdlib\DateTime(),
            $connectionConfig
        );

        return $adapterInstance->getConnection($this->serviceLocator->get('Magento\Framework\DB\Logger\Null'));
    }
}
