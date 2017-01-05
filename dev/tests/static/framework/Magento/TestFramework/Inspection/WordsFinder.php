<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $_binaryExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'swf', 'mp3', 'avi', 'mov', 'flv', 'jar', 'zip',
        'eot', 'ttf', 'woff', 'woff2', 'ico', 'svg',
    ];

    /**
     * Copyright string which must be present in every non-binary file
     *
     * @var string
     */
    protected $copyrightString = 'Copyright © 2013-2017 Magento, Inc. All rights reserved.';

    /**
     * Copying string which must be present in every non-binary file right after copyright string
     *
     * @var string
     */
    protected $copyingString = 'See COPYING.txt for license details.';

    /**
     * List of extensions for which copyright check must be skipped
     *
     * @var array
     */
    protected $copyrightSkipExtensions = ['csv', 'json', 'lock', 'md', 'txt'];

    /**
     * List of paths where copyright check must be skipped
     *
     * @var array
     */
    protected $copyrightSkipList = [
        'lib/web/legacy-build.min.js'
    ];

    /**
     * Whether copyright presence should be checked or not
     *
     * @var bool
     */
    protected $isCopyrightChecked;

    /**
     * Words to search for
     *
     * @var array
     */
    protected $_words = [];

    /**
     * Map of whitelisted paths to whitelisted words
     *
     * @var array
     */
    protected $_whitelist = [];

    /**
     * Path to base dir, used to calculate relative paths
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Component Registrar
     *
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * Map of phrase to exclude from the file content
     * 
     * @var  array
     */
    private $exclude = [];

    /**
     * @param string|array $configFiles
     * @param string $baseDir
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     * @param bool $isCopyrightChecked
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    public function __construct($configFiles, $baseDir, $componentRegistrar, $isCopyrightChecked = false)
    {
        if (!is_dir($baseDir)) {
            throw new \Magento\TestFramework\Inspection\Exception("Base directory {$baseDir} does not exist");
        }
        $this->_baseDir = str_replace('\\', '/', realpath($baseDir));
        $this->componentRegistrar = $componentRegistrar;

        // Load config files
        if (!is_array($configFiles)) {
            $configFiles = [$configFiles];
        }
        foreach ($configFiles as $configFile) {
            $this->_loadConfig($configFile);
        }

        // Add config files to whitelist, as they surely contain banned words
        foreach ($configFiles as $configFile) {
            $configFile = str_replace('\\', '/', realpath($configFile));
            $this->_whitelist[$configFile] = [];
        }

        $this->_normalizeWhitelistPaths();

        // Final verifications
        if (!$this->_words) {
            throw new \Magento\TestFramework\Inspection\Exception('No words to check');
        }

        $this->isCopyrightChecked = $isCopyrightChecked;
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
        $words = [];
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _extractWhitelist(\SimpleXMLElement $configXml)
    {
        // Load whitelist entries
        $whitelist = [];
        $exclude = [];
        $nodes = $configXml->xpath('//config/whitelist/item');
        foreach ($nodes as $node) {
            $path = $node->xpath('path');
            if (!$path) {
                throw new \Magento\TestFramework\Inspection\Exception(
                    'A "path" must be defined for the whitelisted item'
                );
            }
            $component = $node->xpath('component');
            if ($component) {
                $componentType = $component[0]->xpath('@type')[0];
                $componentName = $component[0]->xpath('@name')[0];
                $path = $this->componentRegistrar->getPath((string)$componentType, (string)$componentName)
                    . '/' . (string)$path[0];
            } else {
                $path = $this->_baseDir . '/' . (string)$path[0];
            }

            // Words
            $words = [];
            $wordNodes = $node->xpath('word');
            if ($wordNodes) {
                foreach ($wordNodes as $wordNode) {
                    $words[] = (string)$wordNode;
                }
            }
            $whitelist[$path] = $words;
            
            $excludeNodes = $node->xpath('exclude');
            $excludes = [];
            if ($excludeNodes) {
                foreach ($excludeNodes as $extractNode) {
                    $excludes[] = (string)$extractNode;
                }
            }
            
            if (isset($exclude[$path])) {
                $exclude[$path] = array_merge($excludes, $exclude[$path]) ;
            } else {
                $exclude[$path] = $excludes;
            }
        }

        // Merge with already present whitelist
        foreach ($whitelist as $newPath => $newWords) {
            if (isset($this->_whitelist[$newPath])) {
                $newWords = array_merge($this->_whitelist[$newPath], $newWords);
            }
            $this->_whitelist[$newPath] = array_unique($newWords);
        }
        
        foreach ($exclude as $newPath => $newWords) {
            if (isset($this->exclude[$newPath])) {
                $newWords = array_merge($this->exclude[$newPath], $newWords);
            }
            $this->exclude[$newPath] = array_unique($newWords);
        }
        return $this;
    }

    /**
     * Normalize whitelist paths, so that they containt only native directory separators
     */
    protected function _normalizeWhitelistPaths()
    {
        $whitelist = $this->_whitelist;
        $this->_whitelist = [];
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
            return [];
        }

        return self::_removeWhitelistedWords($file, $foundWords);
    }

    /**
     * Checks the file content and name against the list of words. Do not check content of binary files.
     *
     * @param  string $file
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _findWords($file)
    {
        $checkContents = !$this->_isBinaryFile($file);
        $path = $this->getSearchablePath($file);
        $contents = $checkContents ? file_get_contents($file) : '';
        if (isset($this->exclude[$file]) && !empty($this->exclude[$file])) {
            foreach ($this->exclude[$file] as $stringToEliminate) {
                $contents = str_replace($stringToEliminate, "", $contents);
            }
        }

        $foundWords = [];
        foreach ($this->_words as $word) {
            if (stripos($path, $word) !== false || stripos($contents, $word) !== false) {
                $foundWords[] = $word;
            }
        }
        if ($contents && $this->isCopyrightChecked && !$this->isCopyrightCheckSkipped($file)
            && (($copyrightStringPosition = mb_strpos($contents, $this->copyrightString)) === false
            || ($copyingStringPosition = strpos($contents, $this->copyingString)) === false
            || $copyingStringPosition - $copyrightStringPosition - mb_strlen($this->copyrightString) > 10)
        ) {
            $foundWords[] = 'Copyright string is missing';
        }
        return $foundWords;
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function isCopyrightCheckSkipped($path)
    {
        if (in_array(pathinfo($path, PATHINFO_EXTENSION), $this->copyrightSkipExtensions)) {
            return true;
        }
        foreach ($this->copyrightSkipList as $dir) {
            if (strpos($path, $dir) !== false) {
                return true;
            }
        }
        return false;
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
                return [];
            }
            $foundWords = array_diff($foundWords, $whitelistWords);
        }
        return $foundWords;
    }

    /**
     * Return the path for words search
     *
     * @param string $file
     * @return string
     */
    protected function getSearchablePath($file)
    {
        if (strpos($file, $this->_baseDir) === false) {
            return $file;
        }
        return substr($file, strlen($this->_baseDir) + 1);
    }
}
