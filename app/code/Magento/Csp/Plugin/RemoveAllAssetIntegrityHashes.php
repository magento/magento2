<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Deploy\Console\DeployStaticOptions;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin that removes existing integrity hashes for all assets.
 */
class RemoveAllAssetIntegrityHashes
{
    /**
     * Constant for SRI Hashes filename
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * @var SubresourceIntegrityRepositoryPool
     * @deprecated
     * @see RemoveAllAssetIntegrityHashes::deleteAllSriFiles() - SRI hashes are now stored by area/vendor/theme/locale
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param SubresourceIntegrityRepositoryPool $integrityRepositoryPool
     * @param SubresourceIntegrityCollector $integrityCollector
     * @param Filesystem|null $filesystem
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        SubresourceIntegrityRepositoryPool $integrityRepositoryPool,
        SubresourceIntegrityCollector $integrityCollector,
        ?Filesystem $filesystem = null,
        ?LoggerInterface $logger = null
    ) {
        $this->integrityRepositoryPool = $integrityRepositoryPool;
        $this->integrityCollector = $integrityCollector;
        $this->filesystem = $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Removes existing integrity hashes before static content deploy.
     *
     * For a full deploy (no area/theme/locale constraints) all sri-hashes.json files
     * are deleted. For a partial deploy only the files matching the requested scope
     * are removed so that other deployed themes and locales retain their SRI coverage.
     * The merged-asset cache file is always deleted because it is invalidated whenever
     * any static file changes.
     *
     * @param DeployStaticContent $subject
     * @param array $options
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeploy(
        DeployStaticContent $subject,
        array $options
    ): void {
        if (PHP_SAPI !== 'cli' || $this->isRefreshContentVersionOnly($options)) {
            return;
        }

        $areas = $options[DeployStaticOptions::AREA] ?? [];
        $themes = $options[DeployStaticOptions::THEME] ?? [];
        $locales = $options[DeployStaticOptions::LANGUAGE] ?? [];

        if (empty($areas) && empty($themes) && empty($locales)) {
            $this->deleteAllSriFiles();
        } else {
            $this->deleteSriFilesForScope(
                (array) $areas,
                (array) $themes,
                (array) $locales
            );
        }

        $this->integrityCollector->clear();
    }

    /**
     * Deletes all sri-hashes.json files from the static directory.
     *
     * @return void
     */
    private function deleteAllSriFiles(): void
    {
        try {
            /** @var WriteInterface $staticDir */
            $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

            $patterns = [
                '*/*/*/*/' . self::SRI_FILENAME,
                '*/' . self::SRI_FILENAME,
                self::SRI_FILENAME,
            ];

            foreach ($patterns as $pattern) {
                foreach ($staticDir->search($pattern) as $file) {
                    $staticDir->delete($file);
                }
            }

            $mergedFile = '_cache/merged/' . self::SRI_FILENAME;
            if ($staticDir->isFile($mergedFile)) {
                $staticDir->delete($mergedFile);
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to delete SRI files: ' . $e->getMessage());
        }
    }

    /**
     * Deletes sri-hashes.json files matching the given deploy scope.
     *
     * Each dimension defaults to a wildcard when not constrained so that, for
     * example, specifying only a theme still clears every locale of that theme.
     * The merged-asset cache is always deleted because it covers all themes.
     *
     * @param string[] $areas
     * @param string[] $themes  Values are in "Vendor/theme" format, e.g. "Magento/luma"
     * @param string[] $locales
     * @return void
     */
    private function deleteSriFilesForScope(array $areas, array $themes, array $locales): void
    {
        try {
            /** @var WriteInterface $staticDir */
            $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

            $areaPatterns   = empty($areas)   ? ['*']   : $areas;
            $localePatterns = empty($locales) ? ['*']   : $locales;
            // Themes arrive as "Vendor/theme"; use "*/*" when unconstrained so the
            // pattern still has the correct number of path segments.
            $themePatterns  = empty($themes)  ? ['*/*'] : $themes;

            $this->deleteFilesMatchingPatterns($staticDir, $areaPatterns, $themePatterns, $localePatterns);

            // The merged cache is always invalid after any deploy
            $mergedFile = '_cache/merged/' . self::SRI_FILENAME;
            if ($staticDir->isFile($mergedFile)) {
                $staticDir->delete($mergedFile);
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to delete SRI files for scope: ' . $e->getMessage());
        }
    }

    /**
     * Deletes all sri-hashes.json files matching the given area/theme/locale pattern combinations.
     *
     * @param WriteInterface $staticDir
     * @param string[] $areaPatterns
     * @param string[] $themePatterns
     * @param string[] $localePatterns
     * @return void
     */
    private function deleteFilesMatchingPatterns(
        WriteInterface $staticDir,
        array $areaPatterns,
        array $themePatterns,
        array $localePatterns
    ): void {
        foreach ($areaPatterns as $area) {
            foreach ($themePatterns as $theme) {
                foreach ($localePatterns as $locale) {
                    $pattern = $area . '/' . $theme . '/' . $locale . '/' . self::SRI_FILENAME;
                    foreach ($staticDir->search($pattern) as $file) {
                        $staticDir->delete($file);
                    }
                }
            }
        }
    }

    /**
     * Checks if only version refresh is requested.
     *
     * @param array $options
     *
     * @return bool
     */
    private function isRefreshContentVersionOnly(array $options): bool
    {
        return isset($options[DeployStaticOptions::REFRESH_CONTENT_VERSION_ONLY])
            && $options[DeployStaticOptions::REFRESH_CONTENT_VERSION_ONLY];
    }
}
