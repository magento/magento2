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
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\MergeStrategy\FileExists;

/**
 * Plugin to add asset integrity hashes for merged JS files.
 */
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
     */
    public function afterMerge(FileExists $subject, ?string $result, array $assetsToMerge, LocalInterface $resultAsset)
    {
        if ($resultAsset->getContentType() !== 'js') {
            return $result;
        }

        $integrity = $this->integrityFactory->create(
            [
                "data" => [
                    'hash' => $this->hashGenerator->generate($resultAsset->getContent()),
                    'path' => $resultAsset->getPath()
                ]
            ]
        );

        $this->sourceIntegrityRepository->save($integrity);

        return $result;
    }
}
