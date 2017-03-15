<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Type\Db\Pdo;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Factory for Mysql adapter
 */
class MysqlFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface|null $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create instance of Mysql
     *
     * @param string $className
     * @param LoggerInterface $logger
     * @param SelectFactory $selectFactory
     * @param array $config
     * @return Mysql
     * @throws \InvalidArgumentException
     */
    public function create(
        $className,
        LoggerInterface $logger,
        SelectFactory $selectFactory,
        array $config
    ) {
        if ($className instanceof Mysql) {
            throw new \InvalidArgumentException('Invalid class, class must extend ' . Mysql::class . '.');
        }
        return $this->objectManager->create(
            $className,
            [
                'logger' => $logger,
                'selectFactory' => $selectFactory,
                'config' => $config,
                'serializer' => $this->objectManager->get(SerializerInterface::class)
            ]
        );
    }
}
