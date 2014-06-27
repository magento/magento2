<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\Language;

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
     * @var array
     */
    private $packs = array();

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(\Magento\Framework\App\Filesystem $filesystem)
    {
        $this->dir = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::LOCALE_DIR);
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
        $declarations = $this->dir->search('*/*/language.xml');
        foreach ($declarations as $file) {
            list($vendor, $code) = explode('/', $file);
            if ($languageCode == $code) {
                $this->readPackDeclaration($vendor, $code);
            }
        }
        $packs = [];
        $this->collectInheritedPacks($languageCode, $packs);
        uasort($packs, [$this, 'sortInherited']);
        $result = [];
        foreach ($packs as $info) {
            $dictionary = $this->readPackCsv($info['vendor'], $info['code']);
            $result = array_merge($result, $dictionary);
        }
        return $result;
    }

    /**
     * Read declaration of the specified language pack
     *
     * Will recursively load any parent packs
     *
     * @param string $vendor
     * @param string $code
     * @return void
     */
    private function readPackDeclaration($vendor, $code)
    {
        if (isset($this->packs[$code][$vendor])) {
            return;
        }
        $file = "{$vendor}/{$code}/language.xml";
        $dom = new \DOMDocument();
        $xml = $this->dir->readFile($file);
        $dom->loadXML($xml);
        $root = $dom->documentElement;
        $this->assertVendor($vendor, $root);
        $this->assertCode($code, $root);
        $this->packs[$code][$vendor] = [
            'vendor' => $vendor,
            'code' => $code,
            'sort_order' => $this->getSortOrder($root),
        ];
        $use = $this->getUse($root);
        if ($use) {
            foreach ($use as $info) {
                $this->packs[$code][$vendor]['use'][] = $info;
                $this->readPackDeclaration($info['vendor'], $info['code']);
            }
        }
    }

    /**
     * Assert that vendor code in the declaration matches the one discovered in file system
     *
     * @param string $expected
     * @param \DOMElement $root
     * @return void
     * @throws \LogicException
     */
    public static function assertVendor($expected, \DOMElement $root)
    {
        foreach ($root->getElementsByTagName('vendor') as $node) {
            if ($expected != $node->nodeValue) {
                throw new \LogicException('Vendor name mismatch');
            }
            break;
        }
    }

    /**
     * Assert that language code in the declaration matches the one discovered in file system
     *
     * @param string $expected
     * @param \DOMElement $root
     * @return void
     * @throws \LogicException
     */
    public static function assertCode($expected, \DOMElement $root)
    {
        foreach ($root->getElementsByTagName('code') as $node) {
            if ($expected != $node->nodeValue) {
                throw new \LogicException('Language code name mismatch');
            }
            break;
        }
    }

    /**
     * Read sort order from the declaration
     *
     * By default will be 0
     *
     * @param \DOMElement $root
     * @return int
     */
    private function getSortOrder(\DOMElement $root)
    {
        foreach ($root->getElementsByTagName('sort_order') as $node) {
            return (int)$node->nodeValue;
        }
        return 0;
    }

    /**
     * Read information about reusing other packs from the declaration
     *
     * @param \DOMElement $root
     * @return array
     */
    private function getUse(\DOMElement $root)
    {
        $result = [];
        foreach ($root->getElementsByTagName('use') as $parent) {
            $result[] = [
                'vendor' => $parent->getAttribute('vendor'),
                'code' => $parent->getAttribute('code'),
            ];
        }
        return $result;
    }

    /**
     * Line up (flatten) a tree of inheritance of language packs
     *
     * Record level of recursion (level of inheritance) for further use in sorting
     *
     * @param string $code
     * @param array $result
     * @param int $level
     * @return void
     */
    private function collectInheritedPacks($code, array &$result, $level = 0)
    {
        if (isset($this->packs[$code])) {
            foreach ($this->packs[$code] as $vendor => $info) {
                $info['inheritance_level'] = $level;
                $result["{$code}|{$vendor}"] = $info;
                if (isset($info['use'])) {
                    foreach ($info['use'] as $reuse) {
                        $this->collectInheritedPacks($reuse['code'], $result, $level + 1);
                    }
                }
            }
        }
    }

    /**
     * Sub-routine for custom sorting packs using inheritance level and sort order
     *
     * First sort by inheritance level descending, then by sort order ascending
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function sortInherited($a, $b)
    {
        if ($a['inheritance_level'] > $b['inheritance_level']) {
            return -1;
        }
        if ($a['inheritance_level'] < $b['inheritance_level']) {
            return 1;
        }
        if ($a['sort_order'] > $b['sort_order']) {
            return 1;
        }
        if ($a['sort_order'] < $b['sort_order']) {
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
     * @param string $code
     * @return array
     */
    private function readPackCsv($vendor, $code)
    {
        $files = $this->dir->search("{$vendor}/{$code}/*.csv");
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
