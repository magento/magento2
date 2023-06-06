<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for \Magento\Framework\Mail\TransportInterface
 *
 * @api
 */
class TransportInterfaceFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = TransportInterface::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return TransportInterface
     */
    public function create(array $data = []): TransportInterface
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
