<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Factory for \Magento\Setup\Model\AdminAccount
 */
class AdminAccountFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create object
     *
     * @param AdapterInterface $connection
     * @param array $data
     * @return AdminAccount
     */
    public function create(AdapterInterface $connection, $data)
    {
        return new AdminAccount(
            $connection,
            $this->serviceLocator->get(\Magento\Framework\Encryption\Encryptor::class),
            $data
        );
    }
}
