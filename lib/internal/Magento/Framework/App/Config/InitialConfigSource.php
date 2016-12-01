<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\DataObject;

/**
 * Responsible for reading sources from files: config.dist.php, config.local.php, config.php
 */
class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $configType;

    /**
     * @var string
     */
    private $fileKey;

    /**
     * DataProvider constructor.
     *
     * @param Reader $reader
     * @param string $configType
     * @param string $fileKey
     */
    public function __construct(Reader $reader, $configType, $fileKey)
    {
        $this->reader = $reader;
        $this->configType = $configType;
        $this->fileKey = $fileKey;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        $data = new DataObject($this->reader->load($this->fileKey));
        return $data->getData($this->configType) ?: [];
    }
}
