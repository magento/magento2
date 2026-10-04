<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Deploy\Service\Bundle;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;

class GenerateBundleAssetIntegrity
{
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
     * @deprecated Preserved for backward compatibility but no longer used
     * @see $repositoryPool Used to save integrity hashes directly instead of collecting
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var File
     */
    private File $fileIo;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @var SubresourceIntegrityRepositoryPool|null
     */
    private ?SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param Filesystem $filesystem
     * @param File $fileIo
     * @param LoggerInterface|null $logger
     * @param SubresourceIntegrityRepositoryPool|null $repositoryPool
     */
    public function __construct(
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityCollector $integrityCollector,
        Filesystem $filesystem,
        File $fileIo,
        ?LoggerInterface $logger = null,
        ?SubresourceIntegrityRepositoryPool $repositoryPool = null
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityCollector = $integrityCollector;
        $this->filesystem = $filesystem;
        $this->fileIo = $fileIo;
        $this->logger = $logger ??
            ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->repositoryPool = $repositoryPool ??
            ObjectManager::getInstance()->get(SubresourceIntegrityRepositoryPool::class);
    }

    /**
     * Generate SRI hashes for JS files in the bundle directory.
     *
     * @param Bundle $subject
     * @param string|null $result
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeploy(Bundle $subject, ?string $result, string $area, string $theme, string $locale)
    {
        if (PHP_SAPI === 'cli') {
            try {
                $pubStaticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
                $files = $pubStaticDir->search(
                    $area . "/" . $theme . "/" . $locale . "/" . Bundle::BUNDLE_JS_DIR . "/*.js"
                );

                $context = $area . '/' . $theme . '/' . $locale;
                $repository = $this->repositoryPool->get($context);

                foreach ($files as $file) {
                    try {
                        $bundlePath = $area . '/' . $theme . '/' . $locale .
                            "/" . Bundle::BUNDLE_JS_DIR . '/' . $this->fileIo->getPathInfo($file)['basename'];

                        $integrity = $this->integrityFactory->create(
                            [
                                "data" => [
                                    'hash' => $this->hashGenerator->generate(
                                        $pubStaticDir->readFile($file)
                                    ),
                                    'path' => $bundlePath
                                ]
                            ]
                        );

                        $repository->save($integrity);
                    } catch (\Exception $e) {
                        $this->logger->warning(
                            'GenerateBundleAssetIntegrity: Failed to generate hash for bundle file: '
                            . $e->getMessage()
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    'GenerateBundleAssetIntegrity: Failed to process bundle assets: ' . $e->getMessage()
                );
            }
        }
    }
}
