<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\Repository\Configuration;

use Magento\Framework\App\DeploymentConfig;

class Config implements ConfigInterface
{
    /** @var string */
    public const CONFIG_NODE_PATH = self::OPERATIONS_NODE . '/' . self::CONFIG_NODE . '/';

    /** @var DeploymentConfig */
    private $deploymentConfig;

    /**
     * Config constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        $key = self::OPERATIONS_NODE . "/" . self::STORAGE_NODE;

        /** @var string $storage */
        $storage = $this->deploymentConfig->get(
            $key,
            self::DEFAULT_STORAGE
        );
        return $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->deploymentConfig->get(self::CONFIG_NODE_PATH);
    }
}
