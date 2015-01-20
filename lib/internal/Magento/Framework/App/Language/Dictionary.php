<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * A service for reading language package dictionaries
 */
class Dictionary
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $dir;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var array
     */
    private $packList = [];

    /**
     * @param Filesystem $filesystem
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        Filesystem $filesystem,
        ConfigFactory $configFactory
    ) {
        $this->dir = $filesystem->getDirectoryRead(DirectoryList::LOCALE);
        $this->configFactory = $configFactory;
    }

    /**
     * Load and merge all phrases from language packs by specified code
     *
     * Takes into account inheritance between language packs
     * Returns associative array where key is phrase in the source code and value is its translation
     *
     * @param string $languageCode
     * @return array
     */
    public function getDictionary($languageCode)
    {
        $languages = [];
        $declarations = $this->dir->search('*/*/language.xml');
        foreach ($declarations as $file) {
            $xmlSource = $this->dir->readFile($file);
            $languageConfig = $this->configFactory->create(['source' => $xmlSource]);
            $this->packList[$languageConfig->getVendor()][$languageConfig->getPackage()] = $languageConfig;
            if ($languageConfig->getCode() === $languageCode) {
                $languages[] = $languageConfig;
            }
        }

        // Collect the inherited packages with meta-information of sorting
        $packs = [];
        foreach ($languages as $languageConfig) {
            $this->collectInheritedPacks($languageConfig, $packs);
        }
        uasort($packs, [$this, 'sortInherited']);

        // Merge all packages of translation to one dictionary
        $result = [];
        foreach ($packs as $packInfo) {
            /** @var Config $languageConfig */
            $languageConfig = $packInfo['language'];
            $dictionary = $this->readPackCsv($languageConfig->getVendor(), $languageConfig->getPackage());
            $result = array_merge($result, $dictionary);
        }
        return $result;
    }

    /**
     * Line up (flatten) a tree of inheritance of language packs
     *
     * Record level of recursion (level of inheritance) for further use in sorting
     *
     * @param Config $languageConfig
     * @param array $result
     * @param int $level
     * @return void
     */
    private function collectInheritedPacks($languageConfig, &$result, $level = 0)
    {
        $packKey = implode('|', [$languageConfig->getVendor(), $languageConfig->getPackage()]);
        if (!isset($result[$packKey])) {
            $result[$packKey] = [
                'inheritance_level' => $level,
                'sort_order'        => $languageConfig->getSortOrder(),
                'language'          => $languageConfig,
            ];
            foreach ($languageConfig->getUses() as $reuse) {
                if (isset($this->packList[$reuse['vendor']][$reuse['package']])) {
                    $parentLanguageConfig = $this->packList[$reuse['vendor']][$reuse['package']];
                    $this->collectInheritedPacks($parentLanguageConfig, $result, $level + 1);
                }
            }
        }
    }

    /**
     * Sub-routine for custom sorting packs using inheritance level and sort order
     *
     * First sort by inheritance level descending, then by sort order ascending
     *
     * @param array $current
     * @param array $next
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function sortInherited($current, $next)
    {
        if ($current['inheritance_level'] > $next['inheritance_level']) {
            return -1;
        } elseif ($current['inheritance_level'] < $next['inheritance_level']) {
            return 1;
        }
        if ($current['sort_order'] > $next['sort_order']) {
            return 1;
        } elseif ($current['sort_order'] < $next['sort_order']) {
            return -1;
        }
        return 0;
    }

    /**
     * Read the CSV-files in a language package
     *
     * The files are sorted alphabetically, then each of them is read, and results are recorded into key => value array
     *
     * @param string $vendor
     * @param string $package
     * @return array
     */
    private function readPackCsv($vendor, $package)
    {
        $files = $this->dir->search("{$vendor}/{$package}/*.csv");
        sort($files);
        $result = [];
        foreach ($files as $path) {
            $file = $this->dir->openFile($path);
            while (($row = $file->readCsv()) !== false) {
                $result[$row[0]] = $row[1];
            }
        }
        return $result;
    }
}
