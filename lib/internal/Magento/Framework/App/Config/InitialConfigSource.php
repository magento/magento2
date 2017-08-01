<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\DataObject;

/**
 * Responsible for reading sources from files: config.dist.php, config.local.php, config.php
 * @since 2.2.0
 */
class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * @var Reader
     * @since 2.2.0
     */
    private $reader;

    /**
     * @var string
     * @since 2.2.0
     */
    private $configType;

    /**
     * @var string
     * @deprecated 2.2.0 Initial configs can not be separated since 2.2.0 version
     * @since 2.2.0
     */
    private $fileKey;

    /**
     * DataProvider constructor.
     *
     * @param Reader $reader
     * @param string $configType
     * @param string $fileKey
     * @since 2.2.0
     */
    public function __construct(Reader $reader, $configType, $fileKey = null)
    {
        $this->reader = $reader;
        $this->configType = $configType;
        $this->fileKey = $fileKey;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function get($path = '')
    {
        $data = new DataObject($this->reader->load());
        if ($path !== '' && $path !== null) {
            $path = '/' . $path;
        }
        return $data->getData($this->configType . $path) ?: [];
    }
}
