<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Io;

use Magento\Framework\Filesystem\DriverInterface;
use Symfony\Component\Finder\Tests\Iterator\DateRangeFilterIteratorTest;

/**
 * Filesystem client
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class File extends AbstractIo
{
    /**
     * Save initial working directory
     *
     * @var string
     */
    protected $_iwd;

    /**
     * Use virtual current working directory for application integrity
     *
     * @var string
     */
    protected $_cwd;

    /**
     * Used to grep ls() output
     *
     * @const
     */
    const GREP_FILES = 'files_only';

    /**
     * Used to grep ls() output
     *
     * @const
     */
    const GREP_DIRS = 'dirs_only';

    /**
     * If this variable is set to TRUE, our library will be able to automatically create
     * non-existent directories.
     *
     * @var bool
     * @access protected
     */
    protected $_allowCreateFolders = false;

    /**
     * Stream open file pointer
     *
     * @var resource
     */
    protected $_streamHandler;

    /**
     * Stream mode filename
     *
     * @var string
     */
    protected $_streamFileName;

    /**
     * Stream mode chmod
     *
     * @var string
     */
    protected $_streamChmod;

    /**
     * Lock file
     *
     * @var bool
     */
    protected $_streamLocked = false;

    /**
     * Destruct
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_streamHandler) {
            $this->streamClose();
        }
    }

    /**
     * Lock file
     *
     * @param bool $exclusive
     * @return bool
     */
    public function streamLock($exclusive = true)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        $this->_streamLocked = true;
        $lock = $exclusive ? LOCK_EX : LOCK_SH;
        return flock($this->_streamHandler, $lock);
    }

    /**
     * Unlock file
     *
     * @return bool
     */
    public function streamUnlock()
    {
        if (!$this->_streamHandler || !$this->_streamLocked) {
            return false;
        }
        $this->_streamLocked = false;
        return flock($this->_streamHandler, LOCK_UN);
    }

    /**
     * Binary-safe file read
     *
     * @param int $length
     * @return string|false
     */
    public function streamRead($length = 1024)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        if (feof($this->_streamHandler)) {
            return false;
        }
        return @fgets($this->_streamHandler, $length);
    }

    /**
     * Gets line from file pointer and parse for CSV fields
     *
     * @param string $delimiter
     * @param string $enclosure
     * @return string|false
     */
    public function streamReadCsv($delimiter = ',', $enclosure = '"')
    {
        if (!$this->_streamHandler) {
            return false;
        }
        return @fgetcsv($this->_streamHandler, 0, $delimiter, $enclosure);
    }

    /**
     * Binary-safe file write
     *
     * @param string $str
     * @return int|false
     */
    public function streamWrite($str)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        return @fwrite($this->_streamHandler, $str);
    }

    /**
     * Format line as CSV and write to file pointer
     *
     * @param array $row
     * @param string $delimiter
     * @param string $enclosure
     * @return int|false The length of the written string or false
     */
    public function streamWriteCsv(array $row, $delimiter = ',', $enclosure = '"')
    {
        if (!$this->_streamHandler) {
            return false;
        }
        /**
         * Security enhancement for CSV data processing by Excel-like applications.
         * @see https://bugzilla.mozilla.org/show_bug.cgi?id=1054702
         *
         * @var $value string|\Magento\Framework\Phrase
         */
        foreach ($row as $key => $value) {
            if (!is_string($value)) {
                $value = (string)$value;
            }
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-'])) {
                $row[$key] = ' ' . $value;
            }
        }
        return @fputcsv($this->_streamHandler, $row, $delimiter, $enclosure);
    }

    /**
     * Close an open file pointer
     * Set chmod on a file
     *
     * @return bool
     */
    public function streamClose()
    {
        if (!$this->_streamHandler) {
            return false;
        }

        if ($this->_streamLocked) {
            $this->streamUnlock();
        }
        @fclose($this->_streamHandler);
        $this->chmod($this->_streamFileName, $this->_streamChmod);
        return true;
    }

    /**
     * Retrieve open file statistic
     *
     * @param string $part the part of statistic
     * @param mixed $default default value for part
     * @return array|bool
     */
    public function streamStat($part = null, $default = null)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        $stat = @fstat($this->_streamHandler);
        if ($part !== null) {
            return isset($stat[$part]) ? $stat[$part] : $default;
        }
        return $stat;
    }

    /**
     * Retrieve stream methods exception
     *
     * @return \Exception
     */
    public function getStreamException()
    {
        return $this->_streamException;
    }

    /**
     * Open a connection
     *
     * Possible arguments:
     * - path     default current path
     *
     * @param array $args
     * @return true
     */
    public function open(array $args = [])
    {
        if (!empty($args['path'])) {
            if ($args['path']) {
                if ($this->_allowCreateFolders) {
                    $this->_createDestinationFolder($args['path']);
                }
            }
        }

        $this->_iwd = getcwd();
        $this->cd(!empty($args['path']) ? $args['path'] : $this->_iwd);
        return true;
    }

    /**
     * Used to set {@link _allowCreateFolders} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setAllowCreateFolders($flag)
    {
        $this->_allowCreateFolders = $flag;
        return $this;
    }

    /**
     * Close a connection
     *
     * @return true
     */
    public function close()
    {
        return true;
    }

    /**
     * Create a directory
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dir, $mode = 0777, $recursive = true)
    {
        $this->_cwd();
        $result = @mkdir($dir, $mode, $recursive);
        $this->_iwd();
        return $result;
    }

    /**
     * Delete a directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     */
    public function rmdir($dir, $recursive = false)
    {
        $this->_cwd();
        $result = self::rmdirRecursive($dir, $recursive);
        $this->_iwd();
        return $result;
    }

    /**
     * Delete a directory recursively
     * @param string $dir
     * @param bool $recursive
     * @return bool
     */
    public static function rmdirRecursive($dir, $recursive = true)
    {
        if ($recursive) {
            $result = self::_recursiveCallback($dir, ['unlink'], ['rmdir']);
        } else {
            $result = @rmdir($dir);
        }
        return $result;
    }

    /**
     * Applies specified callback for a directory/file recursively
     *
     * $fileCallback and $dirCallback format: array($callback, $parameters)
     * - $callback - callable
     * - $parameters (optional) - array with parameters to be passed to the $callback
     *
     * @param string $dir
     * @param array $fileCallback
     * @param array $dirCallback
     * @return mixed
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected static function _recursiveCallback($dir, array $fileCallback, array $dirCallback = [])
    {
        if (empty($fileCallback) || !is_array($fileCallback) || !is_array($dirCallback)) {
            throw new \InvalidArgumentException("file/dir callback is not specified");
        }
        if (empty($dirCallback)) {
            $dirCallback = $fileCallback;
        }
        if (is_dir($dir)) {
            foreach (scandir($dir) as $item) {
                if (!strcmp($item, '.') || !strcmp($item, '..')) {
                    continue;
                }
                self::_recursiveCallback($dir . '/' . $item, $fileCallback, $dirCallback);
            }
            $callback = $dirCallback[0];
            if (!is_callable($callback)) {
                throw new \InvalidArgumentException("'dirCallback' parameter is not callable");
            }
            $parameters = isset($dirCallback[1]) ? $dirCallback[1] : [];
        } else {
            $callback = $fileCallback[0];
            if (!is_callable($callback)) {
                throw new \InvalidArgumentException("'fileCallback' parameter is not callable");
            }
            $parameters = isset($fileCallback[1]) ? $fileCallback[1] : [];
        }
        array_unshift($parameters, $dir);
        $result = @call_user_func_array($callback, $parameters);

        return $result;
    }

    /**
     * Get current working directory
     *
     * @return string
     */
    public function pwd()
    {
        return $this->_cwd;
    }

    /**
     * Change current working directory
     *
     * @param string $dir
     * @return true
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function cd($dir)
    {
        if (is_dir($dir)) {
            $this->_iwd();
            $this->_cwd = realpath($dir);
            return true;
        } else {
            throw new \Exception('Unable to list current working directory.');
        }
    }

    /**
     * Read a file to result, file or stream
     *
     * If $dest is null the output will be returned.
     * Otherwise it will be saved to the file or stream and operation result is returned.
     *
     * @param string $filename
     * @param string|resource $dest
     * @return bool|string
     */
    public function read($filename, $dest = null)
    {
        $this->_cwd();
        if ($dest !== null) {
            $result = @copy($filename, $dest);
        } else {
            $result = @file_get_contents($filename);
        }
        $this->_iwd();

        return $result;
    }

    /**
     * Write a file from string, file or stream
     *
     * @param string $filename
     * @param string|resource $src
     * @param int $mode
     * @return int|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function write($filename, $src, $mode = null)
    {
        if (is_string($src) && @is_readable($src)) {
            $src = realpath($src);
            $srcIsFile = true;
        } elseif (is_string($src) || is_resource($src)) {
            $srcIsFile = false;
        } else {
            return false;
        }
        $this->_cwd();

        if (file_exists($filename)) {
            if (!is_writeable($filename)) {
                printf('The file %s is not writable', $filename);
                return false;
            }
        } else {
            if (!is_writable(dirname($filename))) {
                printf('The directory %s is not writable', dirname($filename));
                return false;
            }
        }
        if ($srcIsFile) {
            $result = @copy($src, $filename);
        } else {
            $result = @file_put_contents($filename, $src);
        }
        if ($mode !== null && $result) {
            @chmod($filename, $mode);
        }
        $this->_iwd();
        return $result;
    }

    /**
     * Is file exists
     *
     * @param string $file
     * @param bool $onlyFile
     * @return bool
     */
    public function fileExists($file, $onlyFile = true)
    {
        $this->_cwd();
        $result = file_exists($file);
        if ($result && $onlyFile) {
            $result = is_file($file);
        }
        $this->_iwd();
        return $result;
    }

    /**
     * Tells whether the filename is writable
     *
     * @param string $path
     * @return bool
     */
    public function isWriteable($path)
    {
        $this->_cwd();
        $result = is_writeable($path);
        $this->_iwd();
        return $result;
    }

    /**
     * Get destination folder
     *
     * @param string $filePath
     * @return bool|string
     */
    public function getDestinationFolder($filePath)
    {
        preg_match('/^(.*[!\/])/', $filePath, $matches);
        if (isset($matches[0])) {
            return $matches[0];
        }
        return false;
    }

    /**
     * Create destination folder
     *
     * @param string $path
     * @return bool
     */
    public function createDestinationDir($path)
    {
        if (!$this->_allowCreateFolders) {
            return false;
        }
        return $this->_createDestinationFolder($this->getCleanPath($path));
    }

    /**
     * Check and create if not exists folder
     *
     * @param string $folder
     * @param int $mode
     * @return true
     * @throws \Exception
     */
    public function checkAndCreateFolder($folder, $mode = 0777)
    {
        if (is_dir($folder)) {
            return true;
        }
        if (!is_dir(dirname($folder))) {
            $this->checkAndCreateFolder(dirname($folder), $mode);
        }
        if (!is_dir($folder) && !$this->mkdir($folder, $mode)) {
            throw new \Exception("Unable to create directory '{$folder}'. Access forbidden.");
        }
        return true;
    }

    /**
     * Create destination folder
     *
     * @param string $destinationFolder
     * @return bool
     */
    private function _createDestinationFolder($destinationFolder)
    {
        return $this->checkAndCreateFolder($destinationFolder);
    }

    /**
     * Delete a file
     *
     * @param string $filename
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function rm($filename)
    {
        $this->_cwd();
        $result = @unlink($filename);
        $this->_iwd();
        return $result;
    }

    /**
     * Rename or move a directory or a file
     *
     * @param string $src
     * @param string $destination
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function mv($src, $destination)
    {
        $this->_cwd();
        $result = @rename($src, $destination);
        $this->_iwd();
        return $result;
    }

    /**
     * Copy a file
     *
     * @param string $src
     * @param string $destination
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function cp($src, $destination)
    {
        $this->_cwd();
        $result = @copy($src, $destination);
        $this->_iwd();
        return $result;
    }

    /**
     * Change mode of a directory or a file
     *
     * @param string $filename
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function chmod($filename, $mode, $recursive = false)
    {
        $this->_cwd();
        if ($recursive) {
            $result = self::chmodRecursive($filename, $mode);
        } else {
            $result = @chmod($filename, $mode);
        }
        $this->_iwd();
        return $result;
    }

    /**
     * Change mode of a directory/file recursively
     *
     * @static
     * @param string $dir
     * @param int $mode
     * @return bool
     */
    public static function chmodRecursive($dir, $mode)
    {
        return self::_recursiveCallback($dir, ['chmod', [$mode]]);
    }

    /**
     * Get list of cwd subdirectories and files
     *
     * Suggestions (from moshe):
     * - Use filemtime instead of filectime for performance
     * - Change $grep to $flags and use binary flags
     *   - LS_DIRS  = 1
     *   - LS_FILES = 2
     *   - LS_ALL   = 3
     *
     * @param string|null $grep
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function ls($grep = null)
    {
        $ignoredDirectories = ['.', '..'];

        if (is_dir($this->_cwd)) {
            $dir = $this->_cwd;
        } elseif (is_dir($this->_iwd)) {
            $dir = $this->_iwd;
        } else {
            throw new \Exception('Unable to list current working directory.');
        }

        $list = [];

        $dirHandler = opendir($dir);
        if ($dirHandler) {
            while (($entry = readdir($dirHandler)) !== false) {
                $listItem = [];

                $fullPath = $dir . '/' . $entry;

                if ($grep == self::GREP_DIRS && !is_dir($fullPath)) {
                    continue;
                } elseif ($grep == self::GREP_FILES && !is_file($fullPath)) {
                    continue;
                } elseif (in_array($entry, $ignoredDirectories)) {
                    continue;
                }

                $listItem['text'] = $entry;
                $listItem['mod_date'] = date('Y-m-d H:i:s', filectime($fullPath));
                $listItem['permissions'] = $this->_parsePermissions(fileperms($fullPath));
                $listItem['owner'] = $this->_getFileOwner($fullPath);

                if (is_file($fullPath)) {
                    $pathInfo = pathinfo($fullPath);
                    $listItem['size'] = filesize($fullPath);
                    $listItem['leaf'] = true;
                    if (isset(
                        $pathInfo['extension']
                    ) && in_array(
                        strtolower($pathInfo['extension']),
                        ['jpg', 'jpeg', 'gif', 'bmp', 'png']
                    ) && $listItem['size'] > 0
                    ) {
                        $listItem['is_image'] = true;
                        $listItem['filetype'] = $pathInfo['extension'];
                    } elseif ($listItem['size'] == 0) {
                        $listItem['is_image'] = false;
                        $listItem['filetype'] = 'unknown';
                    } elseif (isset($pathInfo['extension'])) {
                        $listItem['is_image'] = false;
                        $listItem['filetype'] = $pathInfo['extension'];
                    } else {
                        $listItem['is_image'] = false;
                        $listItem['filetype'] = 'unknown';
                    }
                } else {
                    $listItem['leaf'] = false;
                    $listItem['id'] = $fullPath;
                }

                $list[] = $listItem;
            }
            closedir($dirHandler);
        } else {
            throw new \Exception('Unable to list current working directory. Access forbidden.');
        }

        return $list;
    }

    /**
     * Change directory to current working directory
     *
     * @return void
     */
    protected function _cwd()
    {
        if ($this->_cwd) {
            chdir($this->_cwd);
        }
    }

    /**
     * Change directory to initial directory
     *
     * @return void
     */
    protected function _iwd()
    {
        if ($this->_iwd) {
            chdir($this->_iwd);
        }
    }

    /**
     * Convert integer permissions format into human readable
     *
     * @param int $mode
     * @access protected
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parsePermissions($mode)
    {
        if ($mode & 0x1000) {
            $type = 'p'; /* FIFO pipe */
        } elseif ($mode & 0x2000) {
            $type = 'c'; /* Character special */
        } elseif ($mode & 0x4000) {
            $type = 'd'; /* \Directory */
        } elseif ($mode & 0x6000) {
            $type = 'b'; /* Block special */
        } elseif ($mode & 0x8000) {
            $type = '-'; /* Regular */
        } elseif ($mode & 0xA000) {
            $type = 'l'; /* Symbolic Link */
        } elseif ($mode & 0xC000) {
            $type = 's'; /* Socket */
        } else {
            $type = 'u'; /* UNKNOWN */
        }

        /* Determine permissions */
        $owner['read'] = $mode & 00400 ? 'r' : '-';
        $owner['write'] = $mode & 00200 ? 'w' : '-';
        $owner['execute'] = $mode & 00100 ? 'x' : '-';
        $group['read'] = $mode & 00040 ? 'r' : '-';
        $group['write'] = $mode & 00020 ? 'w' : '-';
        $group['execute'] = $mode & 00010 ? 'x' : '-';
        $world['read'] = $mode & 00004 ? 'r' : '-';
        $world['write'] = $mode & 00002 ? 'w' : '-';
        $world['execute'] = $mode & 00001 ? 'x' : '-';

        /* Adjust for SUID, SGID and sticky bit */
        if ($mode & 0x800) {
            $owner["execute"] = $owner['execute'] == 'x' ? 's' : 'S';
        }
        if ($mode & 0x400) {
            $group["execute"] = $group['execute'] == 'x' ? 's' : 'S';
        }
        if ($mode & 0x200) {
            $world["execute"] = $world['execute'] == 'x' ? 't' : 'T';
        }

        $s = sprintf('%1s', $type);
        $s .= sprintf('%1s%1s%1s', $owner['read'], $owner['write'], $owner['execute']);
        $s .= sprintf('%1s%1s%1s', $group['read'], $group['write'], $group['execute']);
        $s .= sprintf('%1s%1s%1s', $world['read'], $world['write'], $world['execute']);
        return trim($s);
    }

    /**
     * Get file owner
     *
     * @param string $filename
     * @return string
     */
    protected function _getFileOwner($filename)
    {
        if (!function_exists('posix_getpwuid')) {
            return 'n/a';
        }

        $owner = posix_getpwuid(fileowner($filename));
        $groupInfo = posix_getgrnam(filegroup($filename));

        return $owner['name'] . ' / ' . $groupInfo;
    }

    /**
     * Get directory separator
     *
     * @return string
     */
    public function dirsep()
    {
        return '/';
    }

    /**
     * Get directory name
     *
     * @param string $file
     * @return string
     */
    public function dirname($file)
    {
        return $this->getCleanPath(dirname($file));
    }

    /**
     * Get directories list by path\
     *
     * @param string $path
     * @param int $flag
     * @return array
     */
    public function getDirectoriesList($path, $flag = GLOB_ONLYDIR)
    {
        return glob($this->getCleanPath($path) . '*', $flag);
    }

    /**
     * Get path info
     *
     * @param string $path
     * @return mixed
     */
    public function getPathInfo($path)
    {
        return pathinfo($path);
    }

    /**
     * Open file in stream mode
     * For set folder for file use open method
     *
     * @param string $fileName
     * @param string $mode
     * @param int $chmod
     * @return bool
     * @throws \Exception
     */
    public function streamOpen($fileName, $mode = 'w+', $chmod = 0666)
    {
        $writeableMode = preg_match('#^[wax]#i', $mode);
        if ($writeableMode && !is_writeable($this->_cwd)) {
            throw new \Exception('Permission denied for write to ' . $this->_cwd);
        }

        if (!ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', 1);
        }

        @chdir($this->_cwd);
        $this->_streamHandler = @fopen($fileName, $mode);
        @chdir($this->_iwd);
        if ($this->_streamHandler === false) {
            throw new \Exception('Error write to file ' . $fileName);
        }

        $this->_streamFileName = $fileName;
        $this->_streamChmod = $chmod;
        return true;
    }
}
