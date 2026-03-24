<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\MergeStrategy\FileExists;

class GenerateMergedAssetIntegrity
{
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
     * @param SubresourceIntegrityRepositoryPool $sourceIntegrityRepositoryPool
     * @param HashGenerator $hashGenerator
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        SubresourceIntegrityRepositoryPool $sourceIntegrityRepositoryPool,
        HashGenerator $hashGenerator,
        SubresourceIntegrityFactory $integrityFactory,
        Filesystem $filesystem
    ) {
        $this->sourceIntegrityRepository = $sourceIntegrityRepositoryPool->get(Area::AREA_FRONTEND);
        $this->hashGenerator = $hashGenerator;
        $this->integrityFactory = $integrityFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Generate SRI hash for merged JS files.
     *
     * @param FileExists $subject
     * @param string|null $result
     * @param array $assetsToMerge
     * @param LocalInterface $resultAsset
     * @return string|null
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
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

        try {
            $this->sourceIntegrityRepository->save($integrity);
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Exception $e) {
        }

        return $result;
    }
}
