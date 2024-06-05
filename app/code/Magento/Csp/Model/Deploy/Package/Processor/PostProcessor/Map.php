<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Deploy\Package\Processor\PostProcessor;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFileFactory;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\RepositoryMap;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Csp\Model\SubresourceIntegrityCollector;

/**
 * Class Adds Integrity attribute to requirejs-map.js asset
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Map extends \Magento\Deploy\Package\Processor\PostProcessor\Map
{

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var Minification
     */
    private Minification $minification;

    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;

    /**
     * @var FileSystem
     */
    private FileSystem $filesystem;

    /**
     * Constructor
     *
     * @param DeployStaticFile $deployStaticFile
     * @param PhpFormatter $formatter
     * @param PackageFileFactory $packageFileFactory
     * @param Minification $minification
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param HashGenerator $hashGenerator
     * @param DriverInterface $driver
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param FileSystem $filesystem
     */
    public function __construct(
        DeployStaticFile $deployStaticFile,
        PhpFormatter $formatter,
        PackageFileFactory $packageFileFactory,
        Minification $minification,
        SubresourceIntegrityFactory $integrityFactory,
        HashGenerator $hashGenerator,
        DriverInterface $driver,
        SubresourceIntegrityCollector $integrityCollector,
        Filesystem $filesystem
    ) {
        $this->minification = $minification;
        $this->integrityFactory = $integrityFactory;
        $this->hashGenerator = $hashGenerator;
        $this->driver = $driver;
        $this->integrityCollector = $integrityCollector;
        $this->filesystem = $filesystem;
        parent::__construct($deployStaticFile, $formatter, $packageFileFactory, $minification);
    }

    /**
     * @inheritdoc
     *
     * @throws FileSystemException
     */
    public function process(Package $package, array $options): bool
    {
        parent::process($package, $options);
        $fileName = $this->minification->addMinifiedSign(RepositoryMap::REQUIRE_JS_MAP_NAME);
        $path = $package->getPath();
        $relativePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if ($this->fileExists($relativePath)) {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
            $absolutePath = $dir->getAbsolutePath($relativePath);
            $fileContent = $this->driver->fileGetContents($absolutePath);

            if ($fileContent) {
                $integrity = $this->integrityFactory->create(
                    [
                        "data" => [
                            'hash' => $this->hashGenerator->generate($fileContent),
                            'path' => $relativePath
                        ]
                    ]
                );
                $this->integrityCollector->collect($integrity);
            }
        }
        return true;
    }

    /**
     * Check if file exist
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    private function fileExists(string $path): bool
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        return $dir->isExist($path);
    }
}
