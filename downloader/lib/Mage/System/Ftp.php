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
 * @category    Mage
 * @package     Mage_System
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with remote FTP server
 *
 * @category    Mage
 * @package     Mage_System
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_System_Ftp
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
     * @throws Exception
     * @return void
     */
    protected function checkConnected()
    {
        if(!$this->_conn) {
            throw new Exception(__CLASS__." - no connection established with server");
        }
    }

    /**
     * ftp_mkdir wrapper
     *
     * @param sting $name
     * @return unknown_type
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
        $dir = explode("/", $path);
        $path= "";
        $ret = true;
        for ($i=0; $i < count($dir); $i++) {
            $path .= "/" .$dir[$i];
            if(!@ftp_chdir($this->_conn, $path)) {
                @ftp_chdir($this->_conn,"/");
                if(!@ftp_mkdir($this->_conn,$path)) {
                    $ret=false;
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
     * @throws Exception on invalid login credentials
     * @return bool
     */
    public function login($login = "anonymous", $password = "")
    {
        $this->checkConnected();
        $res = @ftp_login($this->_conn, $login, $password);
        if(!$res) {
            throw new Exception("Invalid login credentials");
        }
        return $res;
    }

    /**
     * Validate connection string
     *
     * @param string $string
     * @throws Exception
     * @return string
     */
    public function validateConnectionString($string)
    {
        $data = @parse_url($string);
        if(false === $data) {
            throw new Exception("Connection string invalid: '{$string}'");
        }
        if($data['scheme'] != 'ftp') {
            throw new Exception("Support for scheme unsupported: '{$data['scheme']}'");
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
     */
    public function connect($string, $timeout = 900)
    {
        $params = $this->validateConnectionString($string);
        $port = isset($params['port']) ? intval($params['port']) : 21;

        $this->_conn = ftp_connect($params['host'], $port, $timeout);

        if(!$this->_conn) {
            throw new Exception("Cannot connect to host: {$params['host']}");
        }
        if(isset($params['user']) && isset($params['pass'])) {
            $this->login($params['user'], $params['pass']);
        } else {
            $this->login();
        }
        if(isset($params['path'])) {
            if(!$this->chdir($params['path'])) {
                throw new Exception ("Cannot chdir after login to: {$params['path']}");
            }
        }
    }

    /**
     * ftp_fput wrapper
     *
     * @param string $remoteFile
     * @param resource $handle
     * @param int $mode  FTP_BINARY | FTP_ASCII
     * @param int $startPos
     * @return bool
     */
    public function fput($remoteFile, $handle, $mode = FTP_BINARY, $startPos = 0)
    {
        $this->checkConnected();
        return @ftp_fput($this->_conn, $remoteFile, $handle, $mode, $startPos);
    }

    /**
     * ftp_put wrapper
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
     * @return mixed
     */
    public function getcwd()
    {
        $d = $this->raw("pwd");
        $data = explode(" ", $d[0], 3);
        if(empty($data[1])) {
            return false;
        }
        if(intval($data[0]) != 257) {
            return false;
        }
        $out = trim($data[1], '"');
        if($out !== "/") {
            $out = rtrim($out, "/");
        }
        return $out;
    }

    /**
     * ftp_raw wrapper
     *
     * @param string $cmd
     * @return mixed
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
     * @param srting $remote
     * @param string $local
     * @param int $dirMode
     * @param int $ftpMode
     * @return unknown_type
     */
    public function upload($remote, $local, $dirMode = 0777, $ftpMode = FTP_BINARY)
    {
        $this->checkConnected();

        if(!file_exists($local)) {
            throw new Exception("Local file doesn't exist: {$localFile}");
        }
        if(!is_readable($local)) {
            throw new Exception("Local file is not readable: {$localFile}");
        }
        if(is_dir($local)) {
            throw new Exception("Directory given instead of file: {$localFile}");
        }

        $globalPathMode = substr($remote, 0, 1) == "/";
        $dirname = dirname($remote);
        $cwd = $this->getcwd();
        if(false === $cwd) {
            throw new Exception("Server returns something awful on PWD command");
        }

        if(!$globalPathMode) {
            $dirname = $cwd."/".$dirname;
            $remote = $cwd."/".$remote;
        }
        $res = $this->mkdirRecursive($dirname, $dirMode);
        $this->chdir($cwd);

        if(!$res) {
            return false;
        }
        return $this->put($remote, $local, $ftpMode);
    }

    /**
     * Download remote file to local machine
     *
     * @param string $remote
     * @param string $local
     * @param int $ftpMode  FTP_BINARY|FTP_ASCII
     * @return bool
     */
    public function download($remote, $local, $ftpMode = FTP_BINARY)
    {
        $this->checkConnected();
        return $this->get($local, $remote, $ftpMode);
    }

    /**
     * ftp_pasv wrapper
     *
     * @param bool $pasv
     * @return bool
     */
    public function pasv($pasv)
    {
        $this->checkConnected();
        return @ftp_pasv($this->_conn, (bool) $pasv);
    }

    /**
     * Close FTP connection
     *
     * @return void
     */
    public function close()
    {
        if($this->_conn) {
            @ftp_close($this->_conn);
        }
    }

    /**
     * ftp_chmod wrapper
     *
     * @param $mode
     * @param $remoteFile
     * @return bool
     */
    public function chmod($mode, $remoteFile)
    {
        $this->checkConnected();
        return @ftp_chmod($this->_conn, $mode, $remoteFile);
    }

    /**
     * ftp_chdir wrapper
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
     * ftp_cdup wrapper
     *
     * @return bool
     */
    public function cdup()
    {
        $this->checkConnected();
        return @ftp_cdup($this->_conn);
    }

    /**
     * ftp_get wrapper
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int $fileMode         FTP_BINARY | FTP_ASCII
     * @param int $resumeOffset
     * @return bool
     */
    public function get($localFile, $remoteFile, $fileMode = FTP_BINARY, $resumeOffset = 0)
    {
        $remoteFile = $this->correctFilePath($remoteFile);
        $this->checkConnected();
        return @ftp_get($this->_conn, $localFile, $remoteFile, $fileMode, $resumeOffset);
    }

    /**
     * ftp_nlist wrapper
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
     * ftp_rawlist wrapper
     *
     * @param string $dir
     * @param bool $recursive
     * @return mixed
     */
    public function rawlist( $dir = "/", $recursive = false )
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
        $symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $exp = floor( log($bytes) / log(1024) );
        return sprintf( '%.2f ' . $symbol[ $exp ], ($bytes / pow(1024, floor($exp))) );
    }

    /**
     * Chmod string "-rwxrwxrwx" to "777" converter
     *
     * @param string $chmod
     * @return string
     */
    public static function chmodnum($chmod)
    {
        $trans = array('-' => '0', 'r' => '4', 'w' => '2', 'x' => '1');
        $chmod = substr(strtr($chmod, $trans), 1);
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
        $dir = $globalPathMode ? dirname($path) : $this->getcwd()."/".$path;
        $data = $this->ls($dir);
        foreach($data as $row) {
            if($file == $row['name']) {
                if($excludeIfIsDir && $row['dir']) {
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
     */
    public function ls($dir = "/", $recursive = false)
    {
        $dir= $this->correctFilePath($dir);
        $rawfiles = (array) $this->rawlist($dir, $recursive);
        $structure = array();
        $arraypointer = &$structure;
        foreach ($rawfiles as $rawfile) {
            if ($rawfile[0] == '/') {
                $paths = array_slice(explode('/', str_replace(':', '', $rawfile)), 1);
                $arraypointer = &$structure;
                foreach ($paths as $path) {
                    foreach ($arraypointer as $i => $file) {
                        if ($file['name'] == $path) {
                            $arraypointer = &$arraypointer[ $i ]['children'];
                            break;
                        }
                    }
                }
            } elseif(!empty($rawfile)) {
                $info = preg_split("/[\s]+/", $rawfile, 9);
                $arraypointer[] = array(
                    'name'   => $info[8],
                    'dir'  => $info[0]{0} == 'd',
                    'size'   => (int) $info[4],
                    'chmod'  => self::chmodnum($info[0]),
                    'rawdata' => $info,
                    'raw'     => $rawfile
                );
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
