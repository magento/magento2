<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\File;
use Magento\RequireJs\Model\FileManager;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Psr\Log\LoggerInterface;

/**
 * Plugin to add asset integrity value after static content deploy.
 */
class GenerateAssetIntegrity
{
    /**
     * Supported content types.
     *
     * @var array
     */
    private const CONTENT_TYPES = ["js"];

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
     * @deprecated No longer used for hash storage; hashes are stored per-context via $repositoryPool.
     * @see SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param SubresourceIntegrityRepositoryPool|null $repositoryPool
     * @param Filesystem|null $filesystem
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        SubresourceIntegrityCollector $integrityCollector,
        ?SubresourceIntegrityRepositoryPool $repositoryPool = null,
        ?Filesystem $filesystem = null,
        ?LoggerInterface $logger = null
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->integrityCollector = $integrityCollector;
        $this->repositoryPool = $repositoryPool ??
            ObjectManager::getInstance()->get(SubresourceIntegrityRepositoryPool::class);
        $this->filesystem = $filesystem ??
            ObjectManager::getInstance()->get(Filesystem::class);
        $this->logger = $logger ??
            ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Generates integrity for RequireJs config.
     *
     * @param FileManager $subject
     * @param File $result
     *
     * @return File
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsConfigAsset(
        FileManager $subject,
        File $result
    ): File {
        if (PHP_SAPI === 'cli') {
            $this->generateHash($result);
        }

        return $result;
    }

    /**
     * Generates integrity for RequireJs mixins asset.
     *
     * @param FileManager $subject
     * @param File $result
     *
     * @return File
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateRequireJsMixinsAsset(
        FileManager $subject,
        File $result
    ): File {
        if (PHP_SAPI === 'cli') {
            $this->generateHash($result);
        }

        return $result;
    }

    /**
     * Generates integrity for static JS asset.
     *
     * @param FileManager $subject
     * @param File|false $result
     *
     * @return File|false
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateStaticJsAsset(
        FileManager $subject,
        mixed $result
    ): mixed {
        if ($result !== false && PHP_SAPI === 'cli') {
            $this->generateHash($result);
        }

        return $result;
    }

    /**
     * Generates integrity for '.min' files resolver.
     *
     * @param FileManager $subject
     * @param File $result
     *
     * @return File
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateMinResolverAsset(
        FileManager $subject,
        File $result
    ): File {
        if (PHP_SAPI === 'cli') {
            $this->generateHash($result);
        }

        return $result;
    }

    /**
     * Reads raw bytes from disk and saves the SRI hash to the repository pool.
     *
     * @param File $result
     * @return void
     */
    private function generateHash(File $result): void
    {
        if (!in_array($result->getContentType(), self::CONTENT_TYPES)) {
            return;
        }

        $path = $result->getPath();

        try {
            $content = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->readFile($path);

            $pathParts = explode('/', $path);
            if (count($pathParts) < 4) {
                $this->logger->debug('SRI: Skipping invalid path (< 4 segments)', ['path' => $path]);
                return;
            }

            $integrity = $this->integrityFactory->create(
                [
                    "data" => [
                        'hash' => $this->hashGenerator->generate($content),
                        'path' => $path
                    ]
                ]
            );

            $context = implode('/', array_slice($pathParts, 0, 4));
            $this->repositoryPool->get($context)->save($integrity);
        } catch (\Exception $e) {
            $this->logger->error('SRI: Failed to generate hash for ' . $path . ': ' . $e->getMessage());
        }
    }
}
