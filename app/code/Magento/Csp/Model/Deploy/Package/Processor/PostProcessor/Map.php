<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Deploy\Package\Processor\PostProcessor;

use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFileFactory;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\RepositoryMap;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Psr\Log\LoggerInterface;

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
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
     * @param SubresourceIntegrityRepositoryPool|null $repositoryPool
     * @param LoggerInterface|null $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        Filesystem $filesystem,
        ?SubresourceIntegrityRepositoryPool $repositoryPool = null,
        ?LoggerInterface $logger = null
    ) {
        $this->minification = $minification;
        $this->integrityFactory = $integrityFactory;
        $this->hashGenerator = $hashGenerator;
        $this->driver = $driver;
        $this->integrityCollector = $integrityCollector;
        $this->filesystem = $filesystem;
        $this->repositoryPool = $repositoryPool ??
            ObjectManager::getInstance()->get(SubresourceIntegrityRepositoryPool::class);
        $this->logger = $logger ??
            ObjectManager::getInstance()->get(LoggerInterface::class);
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
                // Save immediately to repository instead of using collector
                $area = $package->getArea();

                if (!empty($area)) {
                    try {
                        $this->repositoryPool->get($area)->save($integrity);
                        $this->logger->info("Map PostProcessor: Saved SRI hash for {$relativePath} in {$area} area");
                    } catch (\Exception $e) {
                        //phpcs:ignore
                        $this->logger->error("Map PostProcessor: Failed to save SRI hash for {$relativePath} in {$area} area");
                    }
                }
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
