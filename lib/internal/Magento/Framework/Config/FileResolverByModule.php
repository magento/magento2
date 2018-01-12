<?php
/**
 * Application config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Dir;

/**
 * Resolve all files or file for specific module
 */
class FileResolverByModule extends \Magento\Framework\App\Config\FileResolver
{
    /**
     * This flag says, that we need to read from all modules
     */
    const ALL_MODULES = 'all';

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory,
        ComponentRegistrar $componentRegistrar
    ) {
        parent::__construct($moduleReader, $filesystem, $iteratorFactory);
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * If scope is module
     *
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        $iterator = $this->_moduleReader->getConfigurationFiles($filename)->toArray();
        if ($scope !== self::ALL_MODULES) {
            $path = $this->componentRegistrar->getPath('module', $scope);
            $path .= DIRECTORY_SEPARATOR . Dir::MODULE_ETC_DIR . DIRECTORY_SEPARATOR . $filename;
            $iterator = isset($iterator[$path]) ? [$path => $iterator[$path]] : [];
        }

        return $iterator;
    }
}
