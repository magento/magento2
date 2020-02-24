<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\Repository;

use Magento\AsynchronousOperations\Api\BulkSummaryRepositoryInterface;
use Magento\AsynchronousOperations\Model\Repository\Configuration\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Registry of available connection types
 */
class Registry
{
    /**
     * @var array
     */
    private $repositories;

    /**
     * @var Config
     */
    private $config;

    /**
     * Registry constructor.
     *
     * @param array $repositories
     * @param Config $config
     */
    public function __construct(
        $repositories = [],
        Config $config
    ) {
        $this->repositories = $repositories;
        $this->config = $config;
    }

    /**
     * Get available connection types
     *
     * @return array
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * Get repository based on enabled connection
     *
     * @param $entity
     * @return BulkSummaryRepositoryInterface
     * @throws LocalizedException
     */
    public function getRepository()
    {
        $activeEntityStorage = $this->config->getStorage();
        if (!isset($this->repositories[$activeEntityStorage])) {
            throw new LocalizedException(
                __('%1 is not a valid storage type.', $activeEntityStorage)
            );
        }
        if (!$this->repositories[$activeEntityStorage] instanceof BulkSummaryRepositoryInterface) {
            throw new LocalizedException(
                __('Repository for %1 storage have implement BulkSummaryRepositoryInterface.', $activeEntityStorage)
            );
        }
        return $this->repositories[$activeEntityStorage];
    }
}
