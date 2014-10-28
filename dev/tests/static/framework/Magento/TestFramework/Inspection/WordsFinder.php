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

/**
 * Finder for a list of preconfigured words
 */
namespace Magento\TestFramework\Inspection;

class WordsFinder
{
    /**
     * List of file extensions, that indicate a binary file
     *
     * @var array
     */
    protected $_binaryExtensions = array('jpg', 'jpeg', 'png', 'gif', 'swf', 'mp3', 'avi', 'mov', 'flv', 'jar', 'zip');

    /**
     * Words to search for
     *
     * @var array
     */
    protected $_words = array();

    /**
     * Map of whitelisted paths to whitelisted words
     *
     * @var array
     */
    protected $_whitelist = array();

    /**
     * Path to base dir, used to calculate relative paths
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * @param string|array $configFiles
     * @param string $baseDir
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    public function __construct($configFiles, $baseDir)
    {
        if (!is_dir($baseDir)) {
            throw new \Magento\TestFramework\Inspection\Exception("Base directory {$baseDir} does not exist");
        }
        $this->_baseDir = str_replace('\\', '/', realpath($baseDir));

        // Load config files
        if (!is_array($configFiles)) {
            $configFiles = array($configFiles);
        }
        foreach ($configFiles as $configFile) {
            $this->_loadConfig($configFile);
        }

        // Add config files to whitelist, as they surely contain banned words
        $basePath = $this->_baseDir . '/';
        $basePathLen = strlen($basePath);
        foreach ($configFiles as $configFile) {
            $configFile = str_replace('\\', '/', realpath($configFile));
            if (strncmp($basePath, $configFile, $basePathLen) === 0) {
                // File is inside base dir
                $this->_whitelist[$this->_getRelPath($configFile)] = array();
            }
        }

        $this->_normalizeWhitelistPaths();

        // Final verifications
        if (!$this->_words) {
            throw new \Magento\TestFramework\Inspection\Exception('No words to check');
        }
    }

    /**
     * Load configuration from file, adding words and whitelisted entries to main config
     *
     * @param string $configFile
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    protected function _loadConfig($configFile)
    {
        if (!file_exists($configFile)) {
            throw new \Magento\TestFramework\Inspection\Exception("Configuration file {$configFile} does not exist");
        }
        try {
            $xml = new \SimpleXMLElement(file_get_contents($configFile));
        } catch (\Exception $e) {
            throw new \Magento\TestFramework\Inspection\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->_extractWords($xml)->_extractWhitelist($xml);
    }

    /**
     * Extract words from configuration xml
     *
     * @param \SimpleXMLElement $configXml
     * @return \Magento\TestFramework\Inspection\WordsFinder
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    protected function _extractWords(\SimpleXMLElement $configXml)
    {
        $words = array();
        $nodes = $configXml->xpath('//config/words/word');
        foreach ($nodes as $node) {
            $words[] = (string)$node;
        }
        $words = array_filter($words);

        $words = array_merge($this->_words, $words);
        $this->_words = array_unique($words);
        return $this;
    }

    /**
     * Extract whitelisted entries and words from configuration xml
     *
     * @param \SimpleXMLElement $configXml
     * @return \Magento\TestFramework\Inspection\WordsFinder
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    protected function _extractWhitelist(\SimpleXMLElement $configXml)
    {
        // Load whitelist entries
        $whitelist = array();
        $nodes = $configXml->xpath('//config/whitelist/item');
        foreach ($nodes as $node) {
            $path = $node->xpath('path');
            if (!$path) {
                throw new \Magento\TestFramework\Inspection\Exception(
                    'A "path" must be defined for the whitelisted item'
                );
            }
            $path = (string)$path[0];

            // Words
            $words = array();
            $wordNodes = $node->xpath('word');
            if ($wordNodes) {
                foreach ($wordNodes as $wordNode) {
                    $words[] = (string)$wordNode;
                }
            }

            $whitelist[$path] = $words;
        }

        // Merge with already present whitelist
        foreach ($whitelist as $newPath => $newWords) {
            if (isset($this->_whitelist[$newPath])) {
                $newWords = array_merge($this->_whitelist[$newPath], $newWords);
            }
            $this->_whitelist[$newPath] = array_unique($newWords);
        }

        return $this;
    }

    /**
     * Normalize whitelist paths, so that they containt only native directory separators
     */
    protected function _normalizeWhitelistPaths()
    {
        $whitelist = $this->_whitelist;
        $this->_whitelist = array();
        foreach ($whitelist as $whitelistFile => $whitelistWords) {
            $whitelistFile = str_replace('\\', '/', $whitelistFile);
            $this->_whitelist[$whitelistFile] = $whitelistWords;
        }
    }

    /**
     * Checks the file content and name against the list of words. Do not check content of binary files.
     * Exclude whitelisted entries.
     *
     * @param  string $file
     * @return array Words, found
     */
    public function findWords($file)
    {
        $foundWords = $this->_findWords($file);
        if (!$foundWords) {
            return array();
        }

        $relPath = substr($file, strlen($this->_baseDir) + 1);
        return self::_removeWhitelistedWords($relPath, $foundWords);
    }

    /**
     * Checks the file content and name against the list of words. Do not check content of binary files.
     *
     * @param  string $file
     * @return array
     */
    protected function _findWords($file)
    {
        // MAGETWO-1569: Yaml files are not checked until license placeholder replacement is implemented for them
        $checkContents = !$this->_isBinaryFile($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'yml';

        $relPath = $this->_getRelPath($file);
        $contents = $checkContents ? file_get_contents($file) : '';

        $foundWords = array();
        foreach ($this->_words as $word) {
            if (stripos($relPath, $word) !== false || stripos($contents, $word) !== false) {
                $foundWords[] = $word;
            }
        }
        return $foundWords;
    }

    /**
     * Check, whether file is a binary one
     *
     * @param string $file
     * @return bool
     */
    protected function _isBinaryFile($file)
    {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), $this->_binaryExtensions);
    }

    /**
     * Removes whitelisted words from array of found words
     *
     * @param  string $path
     * @param  array $foundWords
     * @return array
     */
    protected function _removeWhitelistedWords($path, $foundWords)
    {
        $path = str_replace('\\', '/', $path);
        foreach ($this->_whitelist as $whitelistPath => $whitelistWords) {
            if (strncmp($whitelistPath, $path, strlen($whitelistPath)) != 0) {
                continue;
            }

            if (!$whitelistWords) {
                // All words are permitted there
                return array();
            }
            $foundWords = array_diff($foundWords, $whitelistWords);
        }
        return $foundWords;
    }

    /**
     * Return file path relative to base dir
     *
     * @param string $file
     * @return string
     */
    protected function _getRelPath($file)
    {
        return substr($file, strlen($this->_baseDir) + 1);
    }
}
