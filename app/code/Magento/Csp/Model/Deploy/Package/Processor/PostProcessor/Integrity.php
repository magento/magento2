<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Deploy\Package\Processor\PostProcessor;

use Magento\Framework\Filesystem;
use Magento\Deploy\Package\Package;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Deploy\Package\Processor\ProcessorInterface;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Post-processor that generates integrity hashes after static content package deployed.
 */
class Integrity implements ProcessorInterface
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Filesystem $filesystem
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param LoggerInterface|null $logger
     * @param SubresourceIntegrityRepositoryPool|null $repositoryPool
     */
    public function __construct(
        Filesystem $filesystem,
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityCollector $integrityCollector,
        ?LoggerInterface $logger = null,
        ?SubresourceIntegrityRepositoryPool $repositoryPool = null
    ) {
        $this->filesystem = $filesystem;
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityCollector = $integrityCollector;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->repositoryPool = $repositoryPool ??
            ObjectManager::getInstance()->get(SubresourceIntegrityRepositoryPool::class);
    }

    /**
     * @inheritdoc
     */
    public function process(Package $package, array $options): bool
    {
        $staticDir = $this->filesystem->getDirectoryRead(
            DirectoryList::ROOT
        );

        foreach ($package->getFiles() as $file) {
            if (strtolower($file->getExtension()) === "js") {
                $integrity = $this->integrityFactory->create(
                    [
                        "data" => [
                            'hash' => $this->hashGenerator->generate(
                                $staticDir->readFile($file->getSourcePath())
                            ),
                            'path' => $file->getDeployedFilePath()
                        ]
                    ]
                );

                $this->integrityCollector->collect($integrity);
            }
        }

        // Save collected data directly to repository before process exits
        $collectedData = $this->integrityCollector->release();
        if (!empty($collectedData)) {
            $area = explode('/', $package->getPath())[0];
            try {
                $this->repositoryPool->get($area)->saveBunch($collectedData);
            } catch (\Exception $e) {
                //phpcs:ignore
                $this->logger->error('Integrity PostProcessor: Failed saving to ' . $area . ' repository: ' . $e->getMessage());
            }

            // Clear collector for next package (if any)
            $this->integrityCollector->clear();
        }

        return true;
    }
}
