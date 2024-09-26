<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\System;

/**
 * Class to work with remote FTP server
 */
class Ftp
{
    /**
     * Connection object
     *
     * @var resource
     */
    protected $_conn = false;

    /**
     * Check connected, throw exception if not
     *
     * @throws \Exception
     * @return void
     */
    protected function checkConnected()
    {
        if (!$this->_conn) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(__CLASS__ . " - no connection established with server");
        }
    }

    /**
     * Wrapper for ftp_mkdir
     *
     * @param string $name
     * @return string the newly created directory name on success or <b>FALSE</b> on error.
     */
    public function mdkir($name)
    {
        $this->checkConnected();
        return @ftp_mkdir($this->_conn, $name);
    }

    /**
     * Make dir recursive
     *
     * @param string $path
     * @param int $mode
     * @return bool
     */
    public function mkdirRecursive($path, $mode = 0777)
    {
        $this->checkConnected();
        $dir = explode("/", (string)$path);
        $path = "";
        $ret = true;
        for ($i = 0, $count = count($dir); $i < $count; $i++) {
            $path .= "/" . $dir[$i];
            if (!@ftp_chdir($this->_conn, $path)) {
                @ftp_chdir($this->_conn, "/");
                if (!@ftp_mkdir($this->_conn, $path)) {
                    $ret = false;
                    break;
                } else {
                    @ftp_chmod($this->_conn, $mode, $path);
                }
            }
        }
        return $ret;
    }

    /**
     * Try to login to server
     *
     * @param string $login
     * @param string $password
     * @return bool
     * @throws \Exception on invalid login credentials
     */
    public function login($login = "anonymous", $password = "test@gmail.com")
    {
        $this->checkConnected();
        $res = @ftp_login($this->_conn, $login, $password);
        if (!$res) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Invalid login credentials");
        }
        return $res;
    }

    /**
     * Validate connection string
     *
     * @param string $string
     * @throws \Exception
     * @return string
     */
    public function validateConnectionString($string)
    {
        $data = parse_url($string);
        if (false === $data) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Connection string invalid: '{$string}'");
        }
        if ($data['scheme'] != 'ftp') {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Support for scheme unsupported: '{$data['scheme']}'");
        }

        // Decode user & password strings from URL
        foreach (array_intersect(array_keys($data), ['user','pass']) as $key) {
            $data[$key] = urldecode($data[$key]);
        }

        return $data;
    }

    /**
     * Connect to server using connect string
     * Connection string: ftp://user:pass@server:port/path
     * user,pass,port,path are optional parts
     *
     * @param string $string
     * @param int $timeout
     * @return void
     * @throws \Exception
     */
    public function connect($string, $timeout = 900)
    {
        $params = $this->validateConnectionString($string);
        $port = isset($params['port']) ? (int)$params['port'] : 21;

        $this->_conn = ftp_connect($params['host'], $port, $timeout);

        if (!$this->_conn) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Cannot connect to host: {$params['host']}");
        }
        if (isset($params['user']) && isset($params['pass'])) {
            $this->login($params['user'], $params['pass']);
        } else {
            $this->login();
        }
        if (isset($params['path'])) {
            if (!$this->chdir($params['path'])) {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception("Cannot chdir after login to: {$params['path']}");
            }
        }
    }

    /**
     * Wrapper for ftp_fput
     *
     * @param string $remoteFile
     * @param resource $handle
     * @param int $mode FTP_BINARY | FTP_ASCII
     * @param int $startPos
     * @return bool
     */
    public function fput($remoteFile, $handle, $mode = FTP_BINARY, $startPos = 0)
    {
        $this->checkConnected();
        return @ftp_fput($this->_conn, $remoteFile, $handle, $mode, $startPos);
    }

    /**
     * Wrapper for ftp_put
     *
     * @param string $remoteFile
     * @param string $localFile
     * @param int $mode FTP_BINARY | FTP_ASCII
     * @param int $startPos
     * @return bool
     */
    public function put($remoteFile, $localFile, $mode = FTP_BINARY, $startPos = 0)
    {
        $this->checkConnected();
        return ftp_put($this->_conn, $remoteFile, $localFile, $mode, $startPos);
    }

    /**
     * Get current working directory
     *
     * @return false|string
     */
    public function getcwd()
    {
        $d = $this->raw("pwd");
        $data = explode(" ", $d[0] ?? '', 3);
        if (empty($data[1])) {
            return false;
        }
        if ((int)$data[0] != 257) {
            return false;
        }
        $out = trim($data[1], '"');
        if ($out !== "/") {
            $out = rtrim($out, "/");
        }
        return $out;
    }

    /**
     * Wrapper for ftp_raw
     *
     * @param string $cmd
     * @return array The server's response as an array of strings.
     */
    public function raw($cmd)
    {
        $this->checkConnected();
        return @ftp_raw($this->_conn, $cmd);
    }

    /**
     * Upload local file to remote
     *
     * Can be used for relative and absoulte remote paths
     * Relative: use chdir before calling this
     *
     * @param string $remote
     * @param string $local
     * @param int $dirMode
     * @param int $ftpMode
     * @return bool
     * @throws \Exception
     */
    public function upload($remote, $local, $dirMode = 0777, $ftpMode = FTP_BINARY)
    {
        $this->checkConnected();

        if (!file_exists($local)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Local file doesn't exist: {$local}");
        }
        if (!is_readable($local)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Local file is not readable: {$local}");
        }
        if (is_dir($local)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Directory given instead of file: {$local}");
        }

        $globalPathMode = substr((string)$remote, 0, 1) == "/";
        $dirname = dirname($remote);
        $cwd = $this->getcwd();
        if (false === $cwd) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Server returns something awful on PWD command");
        }

        if (!$globalPathMode) {
            $dirname = $cwd . "/" . $dirname;
            $remote = $cwd . "/" . $remote;
        }
        $res = $this->mkdirRecursive($dirname, $dirMode);
        $this->chdir($cwd);

        if (!$res) {
            return false;
        }
        return $this->put($remote, $local, $ftpMode);
    }

    /**
     * Download remote file to local machine
     *
     * @param string $remote
     * @param string $local
     * @param int $ftpMode FTP_BINARY|FTP_ASCII
     * @return bool
     */
    public function download($remote, $local, $ftpMode = FTP_BINARY)
    {
        $this->checkConnected();
        return $this->get($local, $remote, $ftpMode);
    }

    /**
     * Wrapper for ftp_pasv
     *
     * @param bool $pasv
     * @return bool
     */
    public function pasv($pasv)
    {
        $this->checkConnected();
        return @ftp_pasv($this->_conn, (bool)$pasv);
    }

    /**
     * Close FTP connection
     *
     * @return void
     */
    public function close()
    {
        if ($this->_conn) {
            @ftp_close($this->_conn);
        }
    }

    /**
     * Wrapper for ftp_chmod
     *
     * @param int $mode
     * @param string $remoteFile
     * @return int The new file permissions on success or <b>FALSE</b> on error.
     */
    public function chmod($mode, $remoteFile)
    {
        $this->checkConnected();
        return @ftp_chmod($this->_conn, $mode, $remoteFile);
    }

    /**
     * Wrapper for ftp_chdir
     *
     * @param string $dir
     * @return bool
     */
    public function chdir($dir)
    {
        $this->checkConnected();
        return @ftp_chdir($this->_conn, $dir);
    }

    /**
     * Wrapper for ftp_cdup
     *
     * @return bool
     */
    public function cdup()
    {
        $this->checkConnected();
        return @ftp_cdup($this->_conn);
    }

    /**
     * Wrapper for ftp_get
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int $fileMode FTP_BINARY | FTP_ASCII
     * @param int $resumeOffset
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get($localFile, $remoteFile, $fileMode = FTP_BINARY, $resumeOffset = 0)
    {
        $remoteFile = $this->correctFilePath($remoteFile);
        $this->checkConnected();
        return @ftp_get($this->_conn, $localFile, $remoteFile, $fileMode, $resumeOffset);
    }

    /**
     * Wrapper for ftp_nlist
     *
     * @param string $dir
     * @return bool
     */
    public function nlist($dir = "/")
    {
        $this->checkConnected();
        $dir = $this->correctFilePath($dir);
        return @ftp_nlist($this->_conn, $dir);
    }

    /**
     * Wrapper for ftp_rawlist
     *
     * @param string $dir
     * @param bool $recursive
     * @return array an array where each element corresponds to one line of text.
     */
    public function rawlist($dir = "/", $recursive = false)
    {
        $this->checkConnected();
        $dir = $this->correctFilePath($dir);
        return @ftp_rawlist($this->_conn, $dir, $recursive);
    }

    /**
     * Convert byte count to float KB/MB format
     *
     * @param int $bytes
     * @return string
     */
    public static function byteconvert($bytes)
    {
        $symbol = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $exp = floor(log($bytes) / log(1024));
        return sprintf('%.2f ' . $symbol[$exp], $bytes / pow(1024, floor($exp)));
    }

    /**
     * Chmod string "-rwxrwxrwx" to "777" converter
     *
     * @param string $chmod
     * @return string
     */
    public static function chmodnum($chmod)
    {
        $trans = ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1'];
        $chmod = $chmod !== null ? substr(strtr($chmod, $trans), 1) : '';
        $array = str_split($chmod, 3);
        return array_sum(str_split($array[0])) . array_sum(str_split($array[1])) . array_sum(str_split($array[2]));
    }

    /**
     * Check whether file exists
     *
     * @param string $path
     * @param bool $excludeIfIsDir
     * @return bool
     */
    public function fileExists($path, $excludeIfIsDir = true)
    {
        $path = $this->correctFilePath($path);
        $globalPathMode = substr($path, 0, 1) == "/";

        $file = basename($path);
        $dir = $globalPathMode ? dirname($path) : $this->getcwd() . "/" . $path;
        $data = $this->ls($dir);
        foreach ($data as $row) {
            if ($file == $row['name']) {
                if ($excludeIfIsDir && $row['dir']) {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get directory contents in PHP array
     *
     * @param string $dir
     * @param bool $recursive
     * @return array
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function ls($dir = "/", $recursive = false)
    {
        $dir = $this->correctFilePath($dir);
        $rawfiles = (array)$this->rawlist($dir, $recursive);
        $structure = [];
        $arraypointer = & $structure;
        foreach ($rawfiles as $rawfile) {
            if ($rawfile[0] == '/') {
                $paths = array_slice(explode('/', str_replace(':', '', $rawfile)), 1);
                $arraypointer = & $structure;
                foreach ($paths as $path) {
                    foreach ($arraypointer as $i => $file) {
                        if ($file['name'] == $path) {
                            $arraypointer = & $arraypointer[$i]['children'];
                            break;
                        }
                    }
                }
            } elseif (!empty($rawfile)) {
                $info = preg_split("/[\s]+/", $rawfile, 9);
                $arraypointer[] = [
                    'name' => $info[8],
                    'dir' => $info[0][0] == 'd',
                    'size' => (int)$info[4],
                    'chmod' => self::chmodnum($info[0]),
                    'rawdata' => $info,
                    'raw' => $rawfile,
                ];
            }
        }
        return $structure;
    }

    /**
     * Correct file path
     *
     * @param string $str
     * @return string
     */
    public function correctFilePath($str)
    {
        if ($str === null) {
            return '';
        }

        $str = str_replace("\\", "/", $str);
        $str = preg_replace("/^.\//", "", $str);
        return $str;
    }

    /**
     * Delete file
     *
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        $this->checkConnected();
        $file = $this->correctFilePath($file);
        return @ftp_delete($this->_conn, $file);
    }
}
