<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Oauth\Exception;

/**
 * Application config file resolver.
 */
class FileResolverByModule extends \Magento\Framework\App\Config\FileResolver
{
    /**
     * This flag says, that we need to read from all modules.
     */
    const ALL_MODULES = 'all';

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory,
        ComponentRegistrar $componentRegistrar,
        \Magento\Framework\Filesystem\Driver\File $driver
    ) {
        parent::__construct($moduleReader, $filesystem, $iteratorFactory);
        $this->componentRegistrar = $componentRegistrar;
        $this->driver = $driver;
    }

    /**
     * If scope is module.
     *
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        $iterator = $this->_moduleReader->getConfigurationFiles($filename)->toArray();
        if ($scope !== self::ALL_MODULES) {
            $path = $this->componentRegistrar->getPath('module', $scope);
            $path .= '/' . Dir::MODULE_ETC_DIR . '/'. $filename;
            $iterator = isset($iterator[$path]) ? [$path => $iterator[$path]] : [];
        }
        $primaryFile = parent::get($filename, 'primary')->toArray();
        if (!$this->driver->isFile(key($primaryFile))) {
            throw new \Exception("Primary db_schema file doesn`t exists");
        }
        /** Load primary configurations */
        $iterator += $primaryFile;
        return $iterator;
    }
}
