<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\HashResolver;

use Magento\Deploy\Package\Package;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Psr\Log\LoggerInterface;

/**
 * Resolves SRI hashes for static assets across deployment strategies and theme inheritance chains.
 */
class HashResolver implements HashResolverInterface
{
    /**
     * Path to the merged assets SRI hashes file
     */
    private const MERGED_HASHES_FILE = '_cache/merged/sri-hashes.json';

    /**
     * Glob pattern matching result_map.json files written exclusively by compact deploy.
     * Used to detect whether the last SCD run used the compact strategy.
     */
    private const COMPACT_MAP_GLOB = '*/*/*/*/result_map.json';

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * @var State
     */
    private State $appState;

    /**
     * @var DesignInterface
     */
    private DesignInterface $design;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var bool
     */
    private bool $compactDeploy;

    /**
     * @param SubresourceIntegrityRepositoryPool $repositoryPool
     * @param State $appState
     * @param DesignInterface $design
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SubresourceIntegrityRepositoryPool $repositoryPool,
        State $appState,
        DesignInterface $design,
        UrlInterface $urlBuilder,
        LoggerInterface $logger,
        Filesystem $filesystem,
        SerializerInterface $serializer
    ) {
        $this->repositoryPool = $repositoryPool;
        $this->appState = $appState;
        $this->design = $design;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
        $this->compactDeploy = $this->resolveCompactDeploy();
    }

    /**
     * @inheritdoc
     */
    public function getAllHashes(): array
    {
        $result = [];

        try {
            $baseUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC]);

            foreach ($this->getContextsToLoad() as $context) {
                foreach ($this->loadHashesFromContext($context, $baseUrl) as $key => $value) {
                    $result[$key] = $value;
                }
            }

            foreach ($this->loadMergedHashes($baseUrl) as $key => $value) {
                $result[$key] = $value;
            }
        } catch (\Exception $e) {
            $this->logger->warning(
                'SRI: Failed to load all hashes',
                ['exception' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getHashByPath(string $assetPath): ?string
    {
        try {
            foreach ($this->getContextsToLoad() as $context) {
                $hash = $this->findHashInContext($context, $assetPath);
                if ($hash !== null) {
                    return $hash;
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning(
                'SRI: Failed to get hash by path',
                [
                    'asset_path' => $assetPath,
                    'exception' => $e->getMessage()
                ]
            );
        }

        return null;
    }

    /**
     * Read merged-asset hashes directly from disk, bypassing the repository in-memory cache.
     *
     * @param string $baseUrl
     * @return array<string, string>
     */
    private function loadMergedHashes(string $baseUrl): array
    {
        $result = [];

        try {
            $staticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);

            if (!$staticDir->isFile(self::MERGED_HASHES_FILE)) {
                return $result;
            }

            $data = $this->serializer->unserialize($staticDir->readFile(self::MERGED_HASHES_FILE));

            if (!is_array($data)) {
                return $result;
            }

            foreach ($data as $path => $hash) {
                $result[$baseUrl . $path] = $hash;
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                'SRI: Failed to load merged hashes from disk',
                ['exception' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * Loads hashes from the given context into a URL-keyed array.
     *
     * @param string $context
     * @param string $baseUrl
     * @return array
     */
    private function loadHashesFromContext(string $context, string $baseUrl): array
    {
        $result = [];

        try {
            $repository = $this->repositoryPool->get($context);
            foreach ($repository->getAll() as $integrity) {
                $result[$baseUrl . $integrity->getPath()] = $integrity->getHash();
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                'SRI: Failed to load hashes from context',
                [
                    'context' => $context,
                    'exception' => $e->getMessage()
                ]
            );
        }

        return $result;
    }

    /**
     * Finds the hash for an asset path in a specific context.
     *
     * @param string $context
     * @param string $assetPath
     * @return string|null
     */
    private function findHashInContext(string $context, string $assetPath): ?string
    {
        try {
            $repository = $this->repositoryPool->get($context);
            $integrity = $repository->getByPath($assetPath);

            if ($integrity) {
                return $integrity->getHash();
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                'SRI: Failed to find hash in context',
                [
                    'context' => $context,
                    'asset_path' => $assetPath,
                    'exception' => $e->getMessage()
                ]
            );
        }

        return null;
    }

    /**
     * Returns ordered context list for hash lookups.
     *
     * On compact deploys, traverses the full theme inheritance chain so that parent-theme
     * files served from their own URL prefixes are covered.
     *
     * On standard/quick deploys, stops at the active theme because all files are aggregated
     * into the active theme directory and no parent-theme URLs are emitted.
     *
     * @return array<string>
     */
    private function getContextsToLoad(): array
    {
        $contexts = $this->getBaseContexts();

        try {
            $area   = $this->appState->getAreaCode();
            $locale = $this->design->getLocale();
            $theme  = $this->design->getDesignTheme();

            while ($theme) {
                $themePath = $this->design->getThemePath($theme);
                if ($themePath) {
                    array_push($contexts, ...$this->buildThemeContextPaths($area, $themePath, $locale));
                }
                if (!$this->compactDeploy) {
                    break;
                }
                $theme = $theme->getParentTheme();
            }
        } catch (\Exception $e) {
            $this->logger->debug('SRI: Failed to determine theme contexts', ['exception' => $e->getMessage()]);
        }

        return array_unique($contexts);
    }

    /**
     * Returns the base contexts shared across all themes.
     *
     * @return array<string>
     */
    private function getBaseContexts(): array
    {
        $contexts = [Package::BASE_AREA . '/' . Package::BASE_THEME . '/' . Package::BASE_LOCALE];

        try {
            $contexts[] = $this->appState->getAreaCode() . '/' . Package::BASE_THEME . '/' . Package::BASE_LOCALE;
        } catch (\Exception $e) {
            $this->logger->debug('SRI: Failed to determine area context', ['exception' => $e->getMessage()]);
        }

        return $contexts;
    }

    /**
     * Detects whether the last SCD run used the compact strategy.
     *
     * Map.json is written by the Map post-processor exclusively for compact deploy.
     * Standard and quick deploys delete it without recreating it, so its presence
     * is a reliable indicator that the active deployment is compact.
     *
     * Called once in the constructor. Defaults to false when no map.json files exist.
     *
     * @return bool
     */
    private function resolveCompactDeploy(): bool
    {
        try {
            $staticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
            return !empty($staticDir->search(self::COMPACT_MAP_GLOB));
        } catch (\Exception $e) {
            $this->logger->debug(
                'SRI: Failed to detect deploy strategy via map.json',
                ['exception' => $e->getMessage()]
            );
        }

        return false;
    }

    /**
     * Builds the context paths for a single theme and locale.
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return array<string>
     */
    private function buildThemeContextPaths(string $area, string $themePath, string $locale): array
    {
        $contexts = [$area . '/' . $themePath . '/' . Package::BASE_LOCALE];

        if ($locale !== Package::BASE_LOCALE) {
            $contexts[] = $area . '/' . $themePath . '/' . $locale;
        }

        return $contexts;
    }
}
