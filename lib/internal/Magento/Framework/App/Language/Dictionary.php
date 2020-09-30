<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * A service for reading language package dictionaries
 *
 * @api
 * @since 100.0.2
 */
class Dictionary
{
    /**
     * Paths of all language packages
     *
     * @var string[]
     */
    private $paths;

    /**
     * Creates directory read objects
     *
     * @var ReadFactory
     */
    private $directoryReadFactory;

    /**
     * Component Registrar
     *
     * @var ReadFactory
     */
    private $componentRegistrar;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var array
     */
    private $packList = [];

    /**
     * @param ReadFactory $directoryReadFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        ReadFactory $directoryReadFactory,
        ComponentRegistrar $componentRegistrar,
        ConfigFactory $configFactory
    ) {
        $this->directoryReadFactory = $directoryReadFactory;
        $this->componentRegistrar = $componentRegistrar;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDictionary($languageCode)
    {
        $languages = [];
        $this->paths = $this->componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE);
        foreach ($this->paths as $path) {
            $directoryRead = $this->directoryReadFactory->create($path);
            if ($directoryRead->isExist('language.xml')) {
                $xmlSource = $directoryRead->readFile('language.xml');
                try {
                    $languageConfig = $this->configFactory->create(['source' => $xmlSource]);
                } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        new \Magento\Framework\Phrase(
                            'The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.",
                            [$path . '/language.xml', $e->getMessage()]
                        ),
                        $e
                    );
                }
                $this->packList[$languageConfig->getVendor()][$languageConfig->getPackage()] = $languageConfig;
                if ($languageConfig->getCode() === $languageCode) {
                    $languages[] = $languageConfig;
                }
            }
        }

        // Collect the inherited packages with meta-information of sorting
        $packs = [];
        foreach ($languages as $languageConfig) {
            $this->collectInheritedPacks($languageConfig, $packs);
        }

        // Get sorted packs
        $packs = $this->getSortedPacks($packs);

        // Merge all packages of translation to one dictionary
        $result = [];
        foreach ($packs as $packInfo) {
            /** @var Config $languageConfig */
            $languageConfig = $packInfo['language'];
            $dictionary = $this->readPackCsv($languageConfig->getVendor(), $languageConfig->getPackage());
            foreach ($dictionary as $key => $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Get sorted packs
     *
     * First level packs (inheritance_level eq 0) sort by 'sort order' (ascending)
     * Inherited packs has the same order as declared in parent config (language.xml)
     *
     * @param array $allPacks
     *
     * @return array
     */
    private function getSortedPacks($allPacks)
    {
        // Get first level (inheritance_level) packs and sort by provided sort order (descending)
        $firstLevelPacks = array_filter(
            $allPacks,
            function ($pack) {
                return $pack['inheritance_level'] === 0;
            }
        );
        uasort($firstLevelPacks, [$this, 'sortPacks']);

        // Add inherited packs
        $sortedPacks = [];
        foreach ($firstLevelPacks as $pack) {
            $this->addInheritedPacks($allPacks, $pack, $sortedPacks);
        }

        // Reverse array: the first element has the lowest priority, the last one - the highest
        return array_reverse($sortedPacks, true);
    }

    /**
     * Line up (flatten) a tree of inheritance of language packs
     *
     * Record level of recursion (level of inheritance) for further use in sorting
     *
     * @param Config $languageConfig
     * @param array $result
     * @param int $level
     * @param array $visitedPacks
     * @return void
     */
    private function collectInheritedPacks($languageConfig, &$result, $level = 0, array &$visitedPacks = [])
    {
        $packKey = implode('|', [$languageConfig->getVendor(), $languageConfig->getPackage()]);
        if (!isset($visitedPacks[$packKey]) &&
            (!isset($result[$packKey]) || $result[$packKey]['inheritance_level'] < $level)
        ) {
            $visitedPacks[$packKey] = true;
            $result[$packKey] = [
                'inheritance_level' => $level,
                'sort_order'        => $languageConfig->getSortOrder(),
                'language'          => $languageConfig,
                'key'               => $packKey,
            ];
            foreach ($languageConfig->getUses() as $reuse) {
                if (isset($this->packList[$reuse['vendor']][$reuse['package']])) {
                    $parentLanguageConfig = $this->packList[$reuse['vendor']][$reuse['package']];
                    $this->collectInheritedPacks($parentLanguageConfig, $result, $level + 1, $visitedPacks);
                }
            }
        }
    }

    /**
     * Add inherited packs to sorted packs
     *
     * @param array $packs
     * @param array $pack
     * @param array $sortedPacks
     *
     * @return void
     */
    private function addInheritedPacks($packs, $pack, &$sortedPacks)
    {
        if (isset($sortedPacks[$pack['key']])) {
            return;
        }

        $sortedPacks[$pack['key']] = $pack;
        foreach ($pack['language']->getUses() as $reuse) {
            $packKey = implode('|', [$reuse['vendor'], $reuse['package']]);
            if (isset($packs[$packKey])) {
                $this->addInheritedPacks($packs, $packs[$packKey], $sortedPacks);
            }
        }
    }

    /**
     * Sub-routine for custom sorting packs using sort order (descending)
     *
     * @param array $current
     * @param array $next
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function sortPacks($current, $next)
    {
        if ($current['sort_order'] > $next['sort_order']) {
            return -1;
        } elseif ($current['sort_order'] < $next['sort_order']) {
            return 1;
        }
        return strcmp($next['key'], $current['key']);
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
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::LANGUAGE, strtolower($vendor . '_' . $package));
        $result = [];
        if (isset($path)) {
            $directoryRead = $this->directoryReadFactory->create($path);
            $foundCsvFiles = $directoryRead->search("*.csv");
            foreach ($foundCsvFiles as $foundCsvFile) {
                $file = $directoryRead->openFile($foundCsvFile);
                while (($row = $file->readCsv()) !== false) {
                    if (is_array($row) && count($row) > 1) {
                        $result[$row[0]] = $row[1];
                    }
                }
            }
        }
        return $result;
    }
}
