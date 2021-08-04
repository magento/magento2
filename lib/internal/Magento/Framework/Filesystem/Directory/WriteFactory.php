<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\DriverPool;

/**
 * The factory of the filesystem directory instances for write operations.
 */
class WriteFactory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     */
    private $driverPool;

    /**
     * Deny List Validator
     *
     * @var DenyListPathValidator
     */
    private $denyListPathValidator;

    /**
     * Constructor
     *
     * @param DriverPool $driverPool
     * @param DenyListPathValidator|null $denyListPathValidator
     */
    public function __construct(
        DriverPool $driverPool,
        ?DenyListPathValidator $denyListPathValidator = null
    ) {
        $this->driverPool = $driverPool;
        $this->denyListPathValidator = $denyListPathValidator;
    }

    /**
     * Create a writable directory
     *
     * @param string $path
     * @param string $driverCode
     * @param int $createPermissions
     * @return Write
     */
    public function create($path, $driverCode = DriverPool::FILE, $createPermissions = null)
    {
        $driver = $this->driverPool->getDriver($driverCode);
        $factory = new \Magento\Framework\Filesystem\File\WriteFactory(
            $this->driverPool
        );

        if ($this->denyListPathValidator === null) {
            $this->denyListPathValidator = new DenyListPathValidator($driver);
        }

        $validators = [
            'pathValidator' => new PathValidator($driver),
            'denyListPathValidator' => $this->denyListPathValidator
        ];

        $pathValidator = new CompositePathValidator($validators);

        return new Write(
            $factory,
            $driver,
            $path,
            $createPermissions,
            $pathValidator
        );
    }
}
