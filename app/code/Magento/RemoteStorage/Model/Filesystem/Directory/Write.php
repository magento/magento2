<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Filesystem\Directory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\PathValidatorInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Filesystem;

/**
 * Remote storage write class
 */
class Write extends \Magento\Framework\Filesystem\Directory\Write
{
    /**
     * @var WriteInterface
     */
    private $localDirectoryWrite;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileFactory
     * @param DriverInterface $driver
     * @param string $path
     * @param int $createPermissions
     * @param PathValidatorInterface|null $pathValidator
     */
    public function __construct(
        \Magento\Framework\Filesystem\File\WriteFactory $fileFactory,
        DriverInterface $driver,
        $path,
        Filesystem $filesystem,
        $createPermissions = null,
        ?PathValidatorInterface $pathValidator = null
    ) {
        parent::__construct($fileFactory, $driver, $path, $createPermissions, $pathValidator);
        $this->localDirectoryWrite = $filesystem->getDirectoryWrite(
            DirectoryList::PUB,
            DriverPool::FILE
        );
    }

    /**
     * @inheritDoc
     */
    public function delete($path = null)
    {
        $deleted = parent::delete($path);
        if ($deleted) {
            $deleted = $this->localDirectoryWrite->delete($path);
        }
        return $deleted;
    }
}
