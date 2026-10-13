<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\MergeStrategy\FileExists;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateMergedAssetIntegrity
{
    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $sourceIntegrityRepositoryPool;

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $sourceIntegrityRepository;

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var State
     */
    private State $appState;

    /**
     * @param SubresourceIntegrityRepositoryPool $sourceIntegrityRepositoryPool
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param Filesystem $filesystem
     * @param LoggerInterface|null $logger
     * @param State|null $state
     */
    public function __construct(
        SubresourceIntegrityRepositoryPool $sourceIntegrityRepositoryPool,
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        Filesystem $filesystem,
        ?LoggerInterface $logger = null,
        ?State $state = null
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->filesystem = $filesystem;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->appState = $state ?? ObjectManager::getInstance()->get(State::class);
        $this->sourceIntegrityRepositoryPool = $sourceIntegrityRepositoryPool;
    }

    /**
     * Generate SRI hash for merged JS files.
     *
     * @param FileExists $subject
     * @param string|null $result
     * @param array $assetsToMerge
     * @param LocalInterface $resultAsset
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMerge(
        FileExists $subject,
        ?string $result,
        array $assetsToMerge,
        LocalInterface $resultAsset
    ): ?string {
        if ($resultAsset->getContentType() !== 'js') {
            return $result;
        }

        $pubStaticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);

        try {
            $integrity = $this->integrityFactory->create(
                [
                    "data" => [
                        'hash' => $this->hashGenerator->generate(
                            $pubStaticDir->readFile($resultAsset->getPath())
                        ),
                        'path' => $resultAsset->getPath()
                    ]
                ]
            );

            /**
             * Resolved lazily via isset() — area code is unavailable during construction,
             * and accessing an uninitialized typed property throws in PHP 8.
             */
            if (!isset($this->sourceIntegrityRepository)) {
                $this->sourceIntegrityRepository = $this->sourceIntegrityRepositoryPool->get(
                    $this->appState->getAreaCode()
                );
            }
            $this->sourceIntegrityRepository->save($integrity);
        } catch (\Exception $e) {
            $this->logger->warning(
                'GenerateMergedAssetIntegrity: Failed to generate hash for merged file: '
                . $e->getMessage()
            );
        }

        return $result;
    }
}
