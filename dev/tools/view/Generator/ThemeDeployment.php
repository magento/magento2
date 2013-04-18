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
 * @category   Tools
 * @package    view
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Transformation of files, which must be copied to new location and its contents processed
 */
class Generator_ThemeDeployment
{
    /**
     * Destination dir, where files will be copied to
     *
     * @var string
     */
    private $_destinationHomeDir;

    /**
     * List of extensions for files, which should be deployed.
     * For efficiency it is a map of ext => ext, so lookup by hash is possible.
     *
     * @var array
     */
    private $_permitted = array();

    /**
     * List of extensions for files, which must not be deployed
     * For efficiency it is a map of ext => ext, so lookup by hash is possible.
     *
     * @var array
     */
    private $_forbidden = array();

    /**
     * Whether to actually do anything inside the filesystem
     *
     * @var bool
     */
    private $_isDryRun;

    /**
     * Constructor
     *
     * @param string $destinationHomeDir
     * @param string $configPermitted
     * @param string|null $configForbidden
     * @param bool $isDryRun
     * @throws Magento_Exception
     */
    public function __construct($destinationHomeDir, $configPermitted, $configForbidden = null, $isDryRun = false)
    {
        $this->_destinationHomeDir = $destinationHomeDir;
        $this->_isDryRun = $isDryRun;
        $this->_permitted = $this->_loadConfig($configPermitted);
        if ($configForbidden) {
            $this->_forbidden = $this->_loadConfig($configForbidden);
        }
        $conflicts = array_intersect($this->_permitted, $this->_forbidden);
        if ($conflicts) {
            $message = 'Conflicts: the following extensions are added both to permitted and forbidden lists: %s';
            throw new Magento_Exception(sprintf($message, implode(', ', $conflicts)));
        }
    }

    /**
     * Load config with file extensions
     *
     * @param string $path
     * @return array
     * @throws Magento_Exception
     */
    protected function _loadConfig($path)
    {
        if (!file_exists($path)) {
            throw new Magento_Exception("Config file does not exist: {$path}");
        }

        $contents = include($path);
        $contents = array_unique($contents);
        $contents = array_map('strtolower', $contents);
        $contents = $contents ? array_combine($contents, $contents) : array();
        return $contents;
    }

    /**
     * Copy all the files according to $copyRules
     *
     * @param array $copyRules
     */
    public function run($copyRules)
    {
        foreach ($copyRules as $copyRule) {
            $destinationContext = $copyRule['destinationContext'];
            $context = array(
                'source' => $copyRule['source'],
                'destinationContext' => $destinationContext,
            );

            $destDir = Mage_Core_Model_Design_Package::getPublishedViewFileRelPath(
                $destinationContext['area'],
                $destinationContext['themePath'],
                $destinationContext['locale'],
                '',
                $destinationContext['module']
            );
            $destDir = rtrim($destDir, '\\/');

            $this->_copyDirStructure(
                $copyRule['source'],
                $this->_destinationHomeDir . DIRECTORY_SEPARATOR . $destDir,
                $context
            );
        }
    }


    /**
     * Copy dir structure and files from $sourceDir to $destinationDir
     *
     * @param string $sourceDir
     * @param string $destinationDir
     * @param array $context
     * @throws Magento_Exception
     */
    protected function _copyDirStructure($sourceDir, $destinationDir, $context)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $fileSource) {
            $fileSource = (string) $fileSource;
            $extension = strtolower(pathinfo($fileSource, PATHINFO_EXTENSION));

            if (isset($this->_forbidden[$extension])) {
                continue;
            }

            if (!isset($this->_permitted[$extension])) {
                $message = sprintf(
                    'The file extension "%s" must be added either to the permitted or forbidden list. File: %s',
                    $extension,
                    $fileSource
                );
                throw new Magento_Exception($message);
            }

            $fileDestination = $destinationDir . substr($fileSource, strlen($sourceDir));
            $this->_deployFile($fileSource, $fileDestination, $context);
        }
    }

    /**
     * Deploy file to the destination path, also processing modular paths inside css-files.
     *
     * @param string $fileSource
     * @param string $fileDestination
     * @param array $context
     */
    protected function _deployFile($fileSource, $fileDestination, $context)
    {
        $context['fileSource'] = $fileSource;
        $context['fileDestination'] = $fileDestination;

        // Create directory
        $dir = dirname($fileDestination);
        if (!is_dir($dir) && !$this->_isDryRun) {
            mkdir($dir, 0777, true);
        }

        // Copy file
        $extension = pathinfo($fileSource, PATHINFO_EXTENSION);
        if (strtolower($extension) == 'css') {
            // For CSS files we need to replace modular urls
            $content = $this->_processCssContent($fileSource, $context);
            if (!$this->_isDryRun) {
                file_put_contents($fileDestination, $content);
            }
        } else {
            if (!$this->_isDryRun) {
                copy($fileSource, $fileDestination);
            }
        }
    }

    /**
     * Processes CSS file contents, replacing modular urls to the appropriate values
     *
     * @param string $fileSource
     * @param array $context
     * @return string
     */
    protected function _processCssContent($fileSource, $context)
    {
        $content = file_get_contents($fileSource);
        $relativeUrls = $this->_extractModuleUrls($content);
        foreach ($relativeUrls as $urlNotation => $moduleUrl) {
            $fileUrlNew = $this->_expandModuleUrl($moduleUrl, $context);
            $urlNotationNew = str_replace($moduleUrl, $fileUrlNew, $urlNotation);
            $content = str_replace($urlNotation, $urlNotationNew, $content);
        }
        return $content;
    }

    /**
     * Extract module urls (e.g. Mage_Cms::images/something.png) from the css file content
     *
     * @param string $cssContent
     * @return array
     */
    protected function _extractModuleUrls($cssContent)
    {
        preg_match_all(Mage_Core_Model_Design_Package::REGEX_CSS_RELATIVE_URLS, $cssContent, $matches);
        if (empty($matches[0]) || empty($matches[1])) {
            return array();
        }
        $relativeUrls = array_combine($matches[0], $matches[1]);

        // Leave only modular urls
        foreach ($relativeUrls as $key => $relativeUrl) {
            if (!strpos($relativeUrl, Mage_Core_Model_Design_Package::SCOPE_SEPARATOR)) {
                unset($relativeUrls[$key]);
            }
        }

        return $relativeUrls;
    }

    /**
     * Changes module url to normal relative url (it will be relative to the destination file location)
     *
     * @param string $moduleUrl
     * @param array $context
     * @return string
     */
    protected function _expandModuleUrl($moduleUrl, $context)
    {
        $fileDestination = $context['fileDestination'];
        $destinationContext = $context['destinationContext'];

        list($module, $file) = $this->_extractModuleAndFile($moduleUrl);
        $relPath = Mage_Core_Model_Design_Package::getPublishedViewFileRelPath(
            $destinationContext['area'], $destinationContext['themePath'], $destinationContext['locale'], $file, $module
        );
        $relatedFile =  $this->_destinationHomeDir . DIRECTORY_SEPARATOR . $relPath;

        return $this->_composeUrlOffset($relatedFile, $fileDestination);
    }

    /**
     * Divides module url into module name and file path.
     *
     * @param string $moduleUrl
     * @return array
     * @throws Magento_Exception
     */
    protected function _extractModuleAndFile($moduleUrl)
    {
        $parts = explode(Mage_Core_Model_Design_Package::SCOPE_SEPARATOR, $moduleUrl);
        if ((count($parts) != 2) || !strlen($parts[0]) || !strlen($parts[1])) {
            throw new Magento_Exception("Wrong module url: {$moduleUrl}");
        }
        return $parts;
    }

    /**
     * Returns url offset to $filePath as relative to $baseFilePath
     *
     * @param string $filePath
     * @param string $baseFilePath
     * @return string
     */
    protected function _composeUrlOffset($filePath, $baseFilePath)
    {
        $filePath = str_replace('\\', '/', $filePath);
        $baseFilePath = str_replace('\\', '/', $baseFilePath);

        $partsFile = explode('/', dirname($filePath));
        $partsBase = explode('/', dirname($baseFilePath));

        // Go until paths become different
        while (count($partsFile) && count($partsBase) && ($partsFile[0] == $partsBase[0])) {
            array_shift($partsFile);
            array_shift($partsBase);
        }

        // Add '../' for every left level in $partsBase
        $relDir = '';
        if (count($partsBase)) {
            $relDir = str_repeat('../', count($partsBase));
        }

        // Add subdirs from $partsFile
        if (count($partsFile)) {
            $relDir .= implode('/', $partsFile) . '/';
        }

        // Return resulting path
        return $relDir . basename($filePath);
    }
}
