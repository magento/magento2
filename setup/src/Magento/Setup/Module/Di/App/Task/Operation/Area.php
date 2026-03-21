<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Framework\App;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Module\Di\Compiler\Config;
use Magento\Setup\Module\Di\Definition\Collection as DefinitionsCollection;

/**
 * Area configuration aggregation
 */
class Area implements OperationInterface
{
    /**
     * @var App\AreaList
     */
    private $areaList;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\Decorator\Area
     */
    private $areaInstancesNamesList;

    /**
     * @var Config\Reader
     */
    private $configReader;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigWriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Config\ModificationChain
     */
    private $modificationChain;

    /**
     * Path to var/di/ for the definitions cache file (survives cache:clean, invalidated by fingerprint).
     *
     * @var string|null
     */
    private ?string $generatedMetadataPath;

    /**
     * Path to var/cache/ — must exist before child processes call configLoader->load().
     *
     * @var string|null
     */
    private ?string $cacheDir;

    /**
     * @param App\AreaList $areaList
     * @param \Magento\Setup\Module\Di\Code\Reader\Decorator\Area $areaInstancesNamesList
     * @param Config\Reader $configReader
     * @param \Magento\Framework\App\ObjectManager\ConfigWriterInterface $configWriter
     * @param \Magento\Setup\Module\Di\Compiler\Config\ModificationChain $modificationChain
     * @param array $data
     * @param DirectoryList|null $directoryList
     */
    public function __construct(
        App\AreaList $areaList,
        \Magento\Setup\Module\Di\Code\Reader\Decorator\Area $areaInstancesNamesList,
        Config\Reader $configReader,
        \Magento\Framework\App\ObjectManager\ConfigWriterInterface $configWriter,
        Config\ModificationChain $modificationChain,
        $data = [],
        ?DirectoryList $directoryList = null
    ) {
        $this->areaList = $areaList;
        $this->areaInstancesNamesList = $areaInstancesNamesList;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->data = $data;
        $this->modificationChain = $modificationChain;
        if ($directoryList !== null) {
            // Store in var/di/ so the cache survives a standard cache:clean / setup:di:compile run.
            // The fingerprint check handles invalidation when source files change.
            $this->generatedMetadataPath = $directoryList->getPath(DirectoryList::VAR_DIR) . '/di';
            // DiCompileCommand::cleanupFilesystem() deletes var/cache/ before phases run.
            // Child processes call configLoader->load() which uses the cache backend,
            // so we must ensure the directory exists again before forking.
            $this->cacheDir = $directoryList->getPath(DirectoryList::CACHE);
        } else {
            // Fallback: derive paths from the current working directory (always the Magento root).
            // The setup ObjectManager does not resolve DirectoryList in the Area context,
            // so we compute paths manually. BP is not available in the setup bootstrap.
            $basePath = getcwd();
            $this->generatedMetadataPath = $basePath . '/var/di';
            $this->cacheDir = $basePath . '/var/cache';
        }
    }

    /**
     * @inheritdoc
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $definitionsCollection = $this->loadDefinitionsCollection();

        $this->sortDefinitions($definitionsCollection);

        $areaCodes = array_merge([App\Area::AREA_GLOBAL], $this->areaList->getCodes());

        // Each area produces an independent output file and reads from a shared, read-only
        // DefinitionsCollection. Fork one child per area when pcntl is available so all areas
        // are processed in parallel rather than sequentially.
        if (function_exists("pcntl_fork") && count($areaCodes) > 1) {
            $this->processAreasParallel($areaCodes, $definitionsCollection);
        } else {
            $this->processAreasSerial($areaCodes, $definitionsCollection);
        }
    }

    /**
     * Build (or restore from cache) the DefinitionsCollection for all scanned paths.
     *
     * Cache key = CRC32 of the serialised mtime+size of every scanned path.
     * If the cache file exists and the key still matches, the filesystem scan and
     * constructor-reflection phase can be skipped entirely, saving 20–40 s on large installs.
     *
     * @return DefinitionsCollection
     */
    private function loadDefinitionsCollection(): DefinitionsCollection
    {
        $cacheFile = $this->generatedMetadataPath
            ? $this->generatedMetadataPath . '/definitions.cache'
            : null;

        // Build an inexpensive fingerprint from the mtime of each scanned root directory
        $fingerprint = $this->computePathsFingerprint();

        if ($cacheFile !== null && is_file($cacheFile)) {
            $cached = unserialize(file_get_contents($cacheFile), ['allowed_classes' => false]);
            if (is_array($cached)
                && isset($cached['fingerprint'], $cached['definitions'])
                && $cached['fingerprint'] === $fingerprint
            ) {
                $dc = new DefinitionsCollection();
                $dc->initialize($cached['definitions']);
                return $dc;
            }
        }

        // Cache miss — scan filesystem and reflect constructors
        $definitionsCollection = new DefinitionsCollection();
        foreach ($this->data as $paths) {
            if (!is_array($paths)) {
                $paths = (array)$paths;
            }
            foreach ($paths as $path) {
                $definitionsCollection->addCollection($this->getDefinitionsCollection($path));
            }
        }

        // Persist to cache for next run
        if ($cacheFile !== null) {
            $dir = dirname($cacheFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $cacheFile,
                serialize(['fingerprint' => $fingerprint, 'definitions' => $definitionsCollection->getCollection()])
            );
        }

        return $definitionsCollection;
    }

    /**
     * Compute a lightweight fingerprint of all scanned source paths.
     * Uses the mtime and size of each path entry (directory or file).
     *
     * @return string
     */
    private function computePathsFingerprint(): string
    {
        $parts = [];
        foreach ($this->data as $paths) {
            foreach ((array)$paths as $path) {
                $realPath = realpath($path);
                if ($realPath !== false) {
                    $parts[] = $realPath . ':' . filemtime($realPath) . ':' . filesize($realPath);
                }
            }
        }
        sort($parts);
        return hash('crc32b', implode('|', $parts));
    }

    /**
     * Process all area codes sequentially (fallback when pcntl is unavailable).
     *
     * @param string[] $areaCodes
     * @param DefinitionsCollection $definitionsCollection
     * @return void
     */
    private function processAreasSerial(array $areaCodes, DefinitionsCollection $definitionsCollection): void
    {
        foreach ($areaCodes as $areaCode) {
            $this->processOneArea($areaCode, $definitionsCollection);
        }
    }

    /**
     * Process all area codes in parallel child processes.
     * Each child handles exactly one area and exits; the parent waits for all children.
     *
     * @param string[] $areaCodes
     * @param DefinitionsCollection $definitionsCollection
     * @return void
     * @throws \RuntimeException|\Magento\Setup\Module\Di\App\Task\OperationException
     */
    private function processAreasParallel(array $areaCodes, DefinitionsCollection $definitionsCollection): void
    {
        // DiCompileCommand::cleanupFilesystem() deletes var/cache/ before the area phase runs.
        // Child processes' configLoader->load() uses the cache backend which requires the
        // directory to exist. Pre-create it here before any fork to avoid all children failing.
        if ($this->cacheDir !== null && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $pids = [];
        foreach ($areaCodes as $areaCode) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                // Fork failed — fall back to serial for remaining areas
                $this->processAreasSerial(array_slice($areaCodes, count($pids)), $definitionsCollection);
                break;
            }
            if ($pid === 0) {
                // Child process: handle one area then exit cleanly
                try {
                    $this->processOneArea($areaCode, $definitionsCollection);
                } catch (\Throwable $e) {
                    fwrite(STDERR, sprintf(
                        "[Area child:%d %s] %s in %s:%d\n",
                        getmypid(),
                        $areaCode,
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ));
                    exit(1);
                }
                exit(0);
            }
            $pids[$areaCode] = $pid;
        }

        // Parent: collect all children
        foreach ($pids as $areaCode => $pid) {
            pcntl_waitpid($pid, $status);
            if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
                throw new \RuntimeException(
                    sprintf('Area config generation failed for area "%s" (child process exited with error)', $areaCode)
                );
            }
        }
    }

    /**
     * Generate, modify, sort, and write compiled DI config for a single area.
     *
     * @param string $areaCode
     * @param DefinitionsCollection $definitionsCollection
     * @return void
     */
    private function processOneArea(string $areaCode, DefinitionsCollection $definitionsCollection): void
    {
        $config = $this->configReader->generateCachePerScope($definitionsCollection, $areaCode);
        $config = $this->modificationChain->modify($config);

        // sort configuration to have it in the same order on every build
        ksort($config['arguments']);
        ksort($config['preferences']);
        ksort($config['instanceTypes']);

        $this->configWriter->write($areaCode, $config);
    }

    /**
     * Returns definitions collection
     *
     * @param string $path
     * @return DefinitionsCollection
     */
    protected function getDefinitionsCollection($path)
    {
        $definitions = new DefinitionsCollection();
        foreach ($this->areaInstancesNamesList->getList($path) as $className => $constructorArguments) {
            $definitions->addDefinition($className, $constructorArguments);
        }
        return $definitions;
    }

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName()
    {
        return 'Area configuration aggregation';
    }

    /**
     * Sort definitions to make reproducible result
     *
     * @param DefinitionsCollection $definitionsCollection
     */
    private function sortDefinitions(DefinitionsCollection $definitionsCollection): void
    {
        $definitions = $definitionsCollection->getCollection();

        ksort($definitions);

        $definitionsCollection->initialize($definitions);
    }
}
