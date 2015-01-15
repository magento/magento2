<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

/**
 * A model for determining information about setup application
 */
class SetupInfo
{
    /**#@+
     * Initialization parameters for redirecting if the application is not installed
     */
    const PARAM_NOT_INSTALLED_URL_PATH = 'MAGE_NOT_INSTALLED_URL_PATH';
    const PARAM_NOT_INSTALLED_URL = 'MAGE_NOT_INSTALLED_URL';
    /**#@-*/

    /**
     * Default path relative to the project root
     */
    const DEFAULT_PATH = 'setup';

    /**
     * Environment variables
     *
     * @var array
     */
    private $server;

    /**
     * Current document root directory
     *
     * @var string
     */
    private $docRoot;

    /**
     * Project root directory
     *
     * @var string
     */
    private $projectRoot;

    /**
     * Constructor
     *
     * @param array $server
     * @param string $projectRoot
     * @throws \InvalidArgumentException
     */
    public function __construct($server, $projectRoot = '')
    {
        $this->server = $server;
        if (empty($server['DOCUMENT_ROOT'])) {
            throw new \InvalidArgumentException('DOCUMENT_ROOT variable is unavailable.');
        }
        $this->docRoot = rtrim(str_replace('\\', '/', $server['DOCUMENT_ROOT']), '/');
        $this->projectRoot = $projectRoot ?: $this->detectProjectRoot();
        $this->projectRoot = str_replace('\\', '/', $this->projectRoot);
    }

    /**
     * Automatically detects project root from current environment
     *
     * Assumptions:
     * if the current setup application relative path is at the end of script path, then it is setup application
     * otherwise it is the "main" application
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function detectProjectRoot()
    {
        if (empty($this->server['SCRIPT_FILENAME'])) {
            throw new \InvalidArgumentException('Project root cannot be automatically detected.');
        }
        $haystack = str_replace('\\', '/', dirname($this->server['SCRIPT_FILENAME']));
        $needle = '/' . $this->getPath();
        $isSetupApp = preg_match('/^(.+?)' . preg_quote($needle, '/') . '$/', $haystack, $matches);
        if ($isSetupApp) {
            return $matches[1];
        }
        return $haystack;
    }

    /**
     * Gets setup application URL
     *
     * @return string
     */
    public function getUrl()
    {
        if (isset($this->server[self::PARAM_NOT_INSTALLED_URL])) {
            return $this->server[self::PARAM_NOT_INSTALLED_URL];
        }
        return Request\Http::getDistroBaseUrlPath($this->server) . $this->getPath() . '/';
    }

    /**
     * Gets the "main" application URL
     *
     * @return string
     */
    public function getProjectUrl()
    {
        $isProjectInDocRoot = false !== strpos($this->projectRoot . '/', $this->docRoot . '/');
        if (!$isProjectInDocRoot || empty($this->server['HTTP_HOST'])) {
            return '';
        }
        return 'http://' . $this->server['HTTP_HOST'] . substr($this->projectRoot . '/', strlen($this->docRoot));
    }

    /**
     * Gets setup application directory path in the filesystem
     *
     * @param string $projectRoot
     * @return string
     */
    public function getDir($projectRoot)
    {
        return rtrim($projectRoot, '/') . '/' . $this->getPath();
    }

    /**
     * Checks if the setup application is available in current document root
     *
     * @return bool
     */
    public function isAvailable()
    {
        $setupDir = $this->getDir($this->projectRoot);
        $isSubDir = false !== strpos($setupDir . '/', $this->docRoot . '/');
        return $isSubDir && realpath($setupDir);
    }

    /**
     * Gets relative path to setup application
     *
     * @return string
     */
    private function getPath()
    {
        if (isset($this->server[self::PARAM_NOT_INSTALLED_URL_PATH])) {
            return trim($this->server[self::PARAM_NOT_INSTALLED_URL_PATH], '/');
        }
        return self::DEFAULT_PATH;
    }
}
