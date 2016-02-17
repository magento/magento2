<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filter for blacklisting and whitelisting of code coverage information.
 *
 * @since Class available since Release 1.0.0
 */
class PHP_CodeCoverage_Filter
{
    /**
     * Source files that are blacklisted.
     *
     * @var array
     */
    private $blacklistedFiles = array();

    /**
     * Source files that are whitelisted.
     *
     * @var array
     */
    private $whitelistedFiles = array();

    /**
     * Adds a directory to the blacklist (recursively).
     *
     * @param string $directory
     * @param string $suffix
     * @param string $prefix
     */
    public function addDirectoryToBlacklist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);

        foreach ($files as $file) {
            $this->addFileToBlacklist($file);
        }
    }

    /**
     * Adds a file to the blacklist.
     *
     * @param string $filename
     */
    public function addFileToBlacklist($filename)
    {
        $this->blacklistedFiles[realpath($filename)] = true;
    }

    /**
     * Adds files to the blacklist.
     *
     * @param array $files
     */
    public function addFilesToBlacklist(array $files)
    {
        foreach ($files as $file) {
            $this->addFileToBlacklist($file);
        }
    }

    /**
     * Removes a directory from the blacklist (recursively).
     *
     * @param string $directory
     * @param string $suffix
     * @param string $prefix
     */
    public function removeDirectoryFromBlacklist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);

        foreach ($files as $file) {
            $this->removeFileFromBlacklist($file);
        }
    }

    /**
     * Removes a file from the blacklist.
     *
     * @param string $filename
     */
    public function removeFileFromBlacklist($filename)
    {
        $filename = realpath($filename);

        if (isset($this->blacklistedFiles[$filename])) {
            unset($this->blacklistedFiles[$filename]);
        }
    }

    /**
     * Adds a directory to the whitelist (recursively).
     *
     * @param string $directory
     * @param string $suffix
     * @param string $prefix
     */
    public function addDirectoryToWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);

        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }

    /**
     * Adds a file to the whitelist.
     *
     * @param string $filename
     */
    public function addFileToWhitelist($filename)
    {
        $this->whitelistedFiles[realpath($filename)] = true;
    }

    /**
     * Adds files to the whitelist.
     *
     * @param array $files
     */
    public function addFilesToWhitelist(array $files)
    {
        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }

    /**
     * Removes a directory from the whitelist (recursively).
     *
     * @param string $directory
     * @param string $suffix
     * @param string $prefix
     */
    public function removeDirectoryFromWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);

        foreach ($files as $file) {
            $this->removeFileFromWhitelist($file);
        }
    }

    /**
     * Removes a file from the whitelist.
     *
     * @param string $filename
     */
    public function removeFileFromWhitelist($filename)
    {
        $filename = realpath($filename);

        if (isset($this->whitelistedFiles[$filename])) {
            unset($this->whitelistedFiles[$filename]);
        }
    }

    /**
     * Checks whether a filename is a real filename.
     *
     * @param  string $filename
     * @return bool
     */
    public function isFile($filename)
    {
        if ($filename == '-' ||
            strpos($filename, 'vfs://') === 0 ||
            strpos($filename, 'xdebug://debug-eval') !== false ||
            strpos($filename, 'eval()\'d code') !== false ||
            strpos($filename, 'runtime-created function') !== false ||
            strpos($filename, 'runkit created function') !== false ||
            strpos($filename, 'assert code') !== false ||
            strpos($filename, 'regexp code') !== false) {
            return false;
        }

        return file_exists($filename);
    }

    /**
     * Checks whether or not a file is filtered.
     *
     * When the whitelist is empty (default), blacklisting is used.
     * When the whitelist is not empty, whitelisting is used.
     *
     * @param  string                     $filename
     * @return bool
     * @throws PHP_CodeCoverage_Exception
     */
    public function isFiltered($filename)
    {
        if (!$this->isFile($filename)) {
            return true;
        }

        $filename = realpath($filename);

        if (!empty($this->whitelistedFiles)) {
            return !isset($this->whitelistedFiles[$filename]);
        }

        return isset($this->blacklistedFiles[$filename]);
    }

    /**
     * Returns the list of blacklisted files.
     *
     * @return array
     */
    public function getBlacklist()
    {
        return array_keys($this->blacklistedFiles);
    }

    /**
     * Returns the list of whitelisted files.
     *
     * @return array
     */
    public function getWhitelist()
    {
        return array_keys($this->whitelistedFiles);
    }

    /**
     * Returns whether this filter has a whitelist.
     *
     * @return bool
     * @since  Method available since Release 1.1.0
     */
    public function hasWhitelist()
    {
        return !empty($this->whitelistedFiles);
    }

    /**
     * Returns the blacklisted files.
     *
     * @return array
     * @since Method available since Release 2.0.0
     */
    public function getBlacklistedFiles()
    {
        return $this->blacklistedFiles;
    }

    /**
     * Sets the blacklisted files.
     *
     * @param array $blacklistedFiles
     * @since Method available since Release 2.0.0
     */
    public function setBlacklistedFiles($blacklistedFiles)
    {
        $this->blacklistedFiles = $blacklistedFiles;
    }

    /**
     * Returns the whitelisted files.
     *
     * @return array
     * @since Method available since Release 2.0.0
     */
    public function getWhitelistedFiles()
    {
        return $this->whitelistedFiles;
    }

    /**
     * Sets the whitelisted files.
     *
     * @param array $whitelistedFiles
     * @since Method available since Release 2.0.0
     */
    public function setWhitelistedFiles($whitelistedFiles)
    {
        $this->whitelistedFiles = $whitelistedFiles;
    }
}
