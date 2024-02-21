<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Csp\Model\SubresourceIntegrityRepository;

/**
 * Class contains file utility functions
 */
class File
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * constructor
     *
     * @param Filesystem $filesystem
     * @param SubresourceIntegrityRepository $integrityRepository
     * @param HashGenerator $hashGenerator
     */
    public function __construct(
        Filesystem $filesystem,
        SubresourceIntegrityRepository $integrityRepository,
        HashGenerator $hashGenerator
    ) {
        $this->filesystem = $filesystem;
        $this->integrityRepository = $integrityRepository;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    private function checkFileExists(string $path): bool
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        return $dir->isExist($path);
    }

    /**
     * Get file content for local asset from file system
     *
     * @param AssetInterface $asset
     * @return string
     * @throws FileSystemException
     */
    private function getFileContents(AssetInterface $asset): string
    {
        $path = $asset instanceof LocalInterface ? $asset->getpath() : '';
        $fileContent = '';

        if ($path && $this->checkFileExists($path)) {
            $fileContent = $asset->getContent();
        }
        return $fileContent;
    }

    /**
     * Gets Hash value of file content
     *
     * @param AssetInterface $asset
     * @return void
     * @throws FileSystemException
     */
    public function generateIntegrity(AssetInterface $asset): void
    {
        $url = $asset->getUrl();
        $integrity = $this->integrityRepository->getByUrl($url);

        if (!$integrity) {
            $fileContent = $this->getFileContents($asset);
            $hash = $fileContent ? $this->hashGenerator->generate($fileContent) : '';
            $data = new SubresourceIntegrity(
                [
                    'hash' => $hash,
                    'url' => $url
                ]
            );
            $this->integrityRepository->save($data);
        }
    }
}
