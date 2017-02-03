<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Filesystem\Io;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Sftp client interface
 *
 * @link        http://www.php.net/manual/en/function.ssh2-connect.php
 */
class Sftp extends AbstractIo
{
    const REMOTE_TIMEOUT = 10;

    const SSH2_PORT = 22;

    /**
     * @var \Net_SFTP $_connection
     */
    protected $_connection = null;

    /**
     * Open a SFTP connection to a remote site.
     *
     * @param array $args Connection arguments
     *        string $args[host] Remote hostname
     *        string $args[username] Remote username
     *        string $args[password] Connection password
     *        int $args[timeout] Connection timeout [=10]
     * @return void
     * @throws \Exception
     */
    public function open(array $args = [])
    {
        if (!isset($args['timeout'])) {
            $args['timeout'] = self::REMOTE_TIMEOUT;
        }
        if (strpos($args['host'], ':') !== false) {
            list($host, $port) = explode(':', $args['host'], 2);
        } else {
            $host = $args['host'];
            $port = self::SSH2_PORT;
        }
        $this->_connection = new \Net_SFTP($host, $port, $args['timeout']);
        if (!$this->_connection->login($args['username'], $args['password'])) {
            throw new \Exception(sprintf("Unable to open SFTP connection as %s@%s", $args['username'], $args['host']));
        }
    }

    /**
     * Close a connection
     *
     * @return void
     */
    public function close()
    {
        $this->_connection->disconnect();
    }

    /**
     * Create a directory
     *
     * @param string $dir
     * @param int $mode ignored here; uses logged-in user's umask
     * @param bool $recursive analogous to mkdir -p
     *
     * Note: if $recursive is true and an error occurs mid-execution,
     * false is returned and some part of the hierarchy might be created.
     * No rollback is performed.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function mkdir($dir, $mode = 0777, $recursive = true)
    {
        if ($recursive) {
            $no_errors = true;
            $dirList = explode('/', $dir);
            reset($dirList);
            $currentWorkingDir = $this->_connection->pwd();
            while ($no_errors && ($dir_item = next($dirList))) {
                $no_errors = $this->_connection->mkdir($dir_item) && $this->_connection->chdir($dir_item);
            }
            $this->_connection->chdir($currentWorkingDir);
            return $no_errors;
        } else {
            return $this->_connection->mkdir($dir);
        }
    }

    /**
     * Delete a directory
     *
     * @param string $dir
     * @param bool $recursive
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function rmdir($dir, $recursive = false)
    {
        if ($recursive) {
            $no_errors = true;
            $currentWorkingDir = $this->pwd();
            if (!$this->_connection->chdir($dir)) {
                throw new \Exception("chdir(): {$dir}: Not a directory");
            }
            $list = $this->_connection->nlist();
            if (!count($list)) {
                // Go back
                $this->_connection->chdir($currentWorkingDir);
                return $this->rmdir($dir, false);
            } else {
                foreach ($list as $filename) {
                    if ($this->_connection->chdir($filename)) {
                        // This is a directory
                        $this->_connection->chdir('..');
                        $no_errors = $no_errors && $this->rmdir($filename, $recursive);
                    } else {
                        $no_errors = $no_errors && $this->rm($filename);
                    }
                }
            }
            $no_errors = $no_errors && ($this->_connection->chdir(
                $currentWorkingDir
            ) && $this->_connection->rmdir(
                $dir
            ));
            return $no_errors;
        } else {
            return $this->_connection->rmdir($dir);
        }
    }

    /**
     * Get current working directory
     *
     * @return mixed
     */
    public function pwd()
    {
        return $this->_connection->pwd();
    }

    /**
     * Change current working directory
     *
     * @param string $dir
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function cd($dir)
    {
        return $this->_connection->chdir($dir);
    }

    /**
     * Read a file
     *
     * @param string $filename remote file name
     * @param string|null $destination local file name (optional)
     * @return mixed
     */
    public function read($filename, $destination = null)
    {
        if (is_null($destination)) {
            $destination = false;
        }
        return $this->_connection->get($filename, $destination);
    }

    /**
     * Write a file
     *
     * @param string $filename
     * @param string $source string data or local file name
     * @param int $mode ignored parameter
     * @return bool
     */
    public function write($filename, $source, $mode = null)
    {
        $mode = is_readable($source) ? NET_SFTP_LOCAL_FILE : NET_SFTP_STRING;
        return $this->_connection->put($filename, $source, $mode);
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
        return $this->_connection->delete($filename);
    }

    /**
     * Rename or move a directory or a file
     *
     * @param string $source
     * @param string $destination
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function mv($source, $destination)
    {
        return $this->_connection->rename($source, $destination);
    }

    /**
     * Change mode of a directory or a file
     *
     * @param string $filename
     * @param int $mode
     * @return mixed
     */
    public function chmod($filename, $mode)
    {
        return $this->_connection->chmod($mode, $filename);
    }

    /**
     * Get list of cwd subdirectories and files
     *
     * @param null $grep ignored parameter
     * @return array
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function ls($grep = null)
    {
        $list = $this->_connection->nlist();
        $currentWorkingDir = $this->pwd();
        $result = [];
        foreach ($list as $name) {
            $result[] = ['text' => $name, 'id' => "{$currentWorkingDir}{$name}"];
        }
        return $result;
    }

    /**
     * Returns a list of files in the current directory
     *
     * @return mixed
     */
    public function rawls()
    {
        $list = $this->_connection->rawlist();
        return $list;
    }
}
