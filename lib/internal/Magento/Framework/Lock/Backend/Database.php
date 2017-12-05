<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Framework\Lock\Backend;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;

class Database implements \Magento\Framework\Lock\LockManagerInterface
{
    /** @var ResourceConnection */
    private $resource;

    /** @var DeploymentConfig */
    private $deploymentConfig;

    /** @var string Lock prefix */
    private $prefix;

    public function __construct(
        ResourceConnection $resource,
        DeploymentConfig $deploymentConfig,
        string $prefix = null
    ) {
        $this->resource = $resource;
        $this->deploymentConfig = $deploymentConfig;
        $this->prefix = $prefix;
    }

    /**
     * Sets a lock for name
     *
     * @param string $name lock name
     * @param int $timeout How long to wait lock acquisition in seconds, negative value means infinite timeout
     * @return bool
     * @throws InputException
     */
    public function acquireLock(string $name, int $timeout = -1): bool
    {
        $name = $this->addPrefix($name);

        return (bool)$this->resource->getConnection()->query("SELECT GET_LOCK(?, ?);", [(string)$name, (int)$timeout])
            ->fetchColumn();
    }

    /**
     * Releases a lock for name
     *
     * @param string $name lock name
     * @return bool
     * @throws InputException
     */
    public function releaseLock(string $name): bool
    {
        $name = $this->addPrefix($name);

        return (bool)$this->resource->getConnection()->query("SELECT RELEASE_LOCK(?);", [(string)$name])->fetchColumn();
    }

    /**
     * Tests of lock is set for name
     *
     * @param string $name lock name
     * @return bool
     * @throws InputException
     */
    public function isLocked(string $name): bool
    {
        $name = $this->addPrefix($name);

        return (bool)$this->resource->getConnection()->query("SELECT IS_USED_LOCK(?);", [(string)$name])->fetchColumn();
    }

    /**
     * Adds prefix and checks for max length of lock name
     *
     * Limited to 64 characters in MySQL.
     *
     * @param string $name
     * @return string $name
     * @throws InputException
     */
    private function addPrefix(string $name): string
    {
        $name = $this->getPrefix() . '|' . $name;

        if (strlen($name) > 64) {
            throw new InputException(new Phrase('Lock name too long'));
        }

        return $name;
    }

    /**
     * Get installation specific lock prefix to avoid lock conflicts
     *
     * @return string lock prefix
     */
    private function getPrefix(): string
    {
        if ($this->prefix === null) {
            $this->prefix = $this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/' . ConfigOptionsListConstants::KEY_NAME,
                ''
            );
        }

        return $this->prefix;
    }
}
