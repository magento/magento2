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
 * @package     Mage_Archive
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with tar archives
 *
 * @category    Mage
 * @package     Mage_Archive
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Archive_Tar extends Mage_Archive_Abstract implements Mage_Archive_Interface
{
    /**
     * Constant is used for parse tar's header.
     */
    const FORMAT_PARSE_HEADER = 'a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix/a12closer';

    /**
     * Keep file or directory for packing.
     *
     * @var string
     */
    protected $_currentFile;

    /**
     * Keep path to file or directory for packing.
     *
     * @var mixed
     */
    protected $_currentPath;

    /**
     * Skip first level parent directory. Example:
     *   use test/fip.php instead test/test/fip.php;
     *
     * @var mixed
     */
    protected $_skipRoot;

    /**
     * Set option that define ability skip first catalog level.
     *
     * @param mixed $skipRoot
     * @return Mage_Archive_Tar
     */
    protected function _setSkipRoot($skipRoot)
    {
        $this->_skipRoot = $skipRoot;
        return $this;
    }

    /**
     * Set file which is packing.
     *
     * @param string $file
     * @return Mage_Archive_Tar
     */
    protected function _setCurrentFile($file)
    {
        $this->_currentFile = $file .((is_dir($file) && substr($file, -1)!=DS)?DS:'');
        return $this;
    }

    /**
     * Retrieve file which is packing.
     *
     * @return string
     */
    protected function _getCurrentFile()
    {
        return $this->_currentFile;
    }

    /**
     * Set path to file which is packing.
     *
     * @param string $path
     * @return Mage_Archive_Tar
     */
    protected function _setCurrentPath($path)
    {
        if ($this->_skipRoot && is_dir($path)) {
            $this->_currentPath = $path.(substr($path, -1)!=DS?DS:'');
        } else {
            $this->_currentPath = dirname($path) . DS;
        }
        return $this;
    }

    /**
     * Retrieve path to file which is packing.
     *
     * @return string
     */
    protected function _getCurrentPath()
    {
        return $this->_currentPath;
    }

    /**
     * Walk through directory and add to tar file or directory.
     * Result is packed string on TAR format.
     *
     * @param boolean $skipRoot
     * @return string
     */
    protected function _packToTar($skipRoot=false)
    {
        $file = $this->_getCurrentFile();
        $header = '';
        $data = '';
        if (!$skipRoot) {
            $header = $this->_composeHeader();
            $data = $this->_readFile($file);
            $data = str_pad($data, floor(((is_dir($file) ? 0 : filesize($file)) + 512 - 1) / 512) * 512, "\0");
        }
        $sub = '';
        if (is_dir($file)) {
            $treeDir = scandir($file);
            if (empty($treeDir)) {
                throw new Mage_Exception('Can\'t scan dir: ' . $file);
            }
            array_shift($treeDir); /* remove  './'*/
            array_shift($treeDir); /* remove  '../'*/
            foreach ($treeDir as $item) {
                $sub .= $this->_setCurrentFile($file.$item)->_packToTar(false);
            }
        }
        $tarData = $header . $data . $sub;
        $tarData = str_pad($tarData, floor((strlen($tarData) - 1) / 1536) * 1536, "\0");
        return $tarData;
    }

    /**
     * Compose header for current file in TAR format.
     * If length of file's name greater 100 characters,
     * method breaks header to two pieces. First conatins
     * header and data with long name. Second contain only header.
     *
     * @param boolean $long
     * @return string
     */
    protected function _composeHeader($long = false)
    {
        $file = $this->_getCurrentFile();
        $path = $this->_getCurrentPath();
        $infoFile = stat($file);
        $nameFile = str_replace($path, '', $file);
        $nameFile = str_replace('\\', '/', $nameFile);
        $packedHeader = '';
        $longHeader = '';
        if (!$long && strlen($nameFile)>100) {
            $longHeader = $this->_composeHeader(true);
            $longHeader .= str_pad($nameFile, floor((strlen($nameFile) + 512 - 1) / 512) * 512, "\0");
        }
        $header = array();
        $header['100-name']       = $long?'././@LongLink':substr($nameFile, 0, 100);
        $header['8-mode']         = $long?'       ':str_pad(substr(sprintf("%07o", $infoFile['mode']),-4), 6, '0', STR_PAD_LEFT);
        $header['8-uid']          = $long || $infoFile['uid']==0?"\0\0\0\0\0\0\0":sprintf("%07o", $infoFile['uid']);
        $header['8-gid']          = $long || $infoFile['gid']==0?"\0\0\0\0\0\0\0":sprintf("%07o", $infoFile['gid']);
        $header['12-size']        = $long?sprintf("%011o", strlen($nameFile)):sprintf("%011o", is_dir($file) ? 0 : filesize($file));
        $header['12-mtime']       = $long?'00000000000':sprintf("%011o", $infoFile['mtime']);
        $header['8-check']        = sprintf('% 8s', '');
        $header['1-type']         = $long?'L':(is_link($file) ? 2 : is_dir ($file) ? 5 : 0);
        $header['100-symlink']    = is_link($file) == 2 ? readlink($item) : '';
        $header['6-magic']        = 'ustar ';
        $header['2-version']      = ' ';
        $a=function_exists('posix_getpwuid')?posix_getpwuid (fileowner($file)):array('name'=>'');
        $header['32-uname']       = $a['name'];
        $a=function_exists('posix_getgrgid')?posix_getgrgid (filegroup($file)):array('name'=>'');
        $header['32-gname']       = $a['name'];
        $header['8-devmajor']     = '';
        $header['8-devminor']     = '';
        $header['155-prefix']     = '';
        $header['12-closer']      = '';

        $packedHeader = '';
        foreach ($header as $key=>$element) {
            $length = explode('-', $key);
            $packedHeader .= pack('a' . $length[0], $element);
        }

        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($packedHeader, $i, 1));
        }
        $packedHeader = substr_replace($packedHeader, sprintf("%07o", $checksum)."\0", 148, 8);

        return $longHeader . $packedHeader;
    }

    /**
     * Read TAR string from file, and unpacked it.
     * Create files and directories information about discribed
     * in the string.
     *
     * @param string $destination path to file is unpacked
     * @return array list of files
     */
    protected function _unpackCurrentTar($destination)
    {
        $file = $this->_getCurrentFile();
        $pointer = fopen($file, 'r');
        if (empty($pointer)) {
            throw new Mage_Exception('Can\'t open file: ' . $file);
        }
        $list = array();
        while (!feof($pointer)) {
            $header = $this->_parseHeader($pointer);
            if ($header) {
                $currentFile = $destination . $header['name'];
                if ($header['type']=='5' && @mkdir($currentFile, 0777, true)) {
                    $list[] = $currentFile . DS;
                } elseif (in_array($header['type'], array("0",chr(0), ''))) {
                    $dirname = dirname($currentFile);
                    if(!file_exists($dirname)) {
                        @mkdir($dirname, 0777, true);
                    }
                    $this->_writeFile($currentFile, $header['data']);
                    $list[] = $currentFile;
                }
            }
        }
        fclose($pointer);
        return $list;
    }

    /**
     * Get header from TAR string and unpacked it by format.
     *
     * @param resource $pointer
     * @return string
     */
    protected function _parseHeader(&$pointer)
    {
        $firstLine = fread($pointer, 512);

        if (strlen($firstLine)<512){
            return false;
        }

        $fmt = self::FORMAT_PARSE_HEADER;
        $header = unpack ($fmt, $firstLine);


        $header['mode']=$header['mode']+0;
        $header['uid']=octdec($header['uid']);
        $header['gid']=octdec($header['gid']);
        $header['size']=octdec($header['size']);
        $header['mtime']=octdec($header['mtime']);
        $header['checksum']=octdec($header['checksum']);

        if ($header['type'] == "5") {
            $header['size'] = 0;
        }

        $checksum = 0;
        $firstLine = substr_replace($firstLine, '        ', 148, 8);
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($firstLine, $i, 1));
        }

        $isUstar = 'ustar' == strtolower(substr($header['magic'], 0, 5));
       
        $checksumOk = $header['checksum'] == $checksum;
        if (isset($header['name']) && $checksumOk) {
            if ($header['name'] == '././@LongLink' && $header['type'] == 'L') {
                $realName = substr(fread($pointer, floor(($header['size'] + 512 - 1) / 512) * 512), 0, $header['size']);
                $headerMain = $this->_parseHeader($pointer);
                $headerMain['name'] = $realName;
                return $headerMain;
            } else {
                if ($header['size']>0) {
                    $header['data'] = substr(fread($pointer, floor(($header['size'] + 512 - 1) / 512) * 512), 0, $header['size']);
                } else {
                    $header['data'] = '';
                }
                return $header;
            }
        }
        return false;
    }

    /**
     * Pack file to TAR (Tape Archiver).
     *
     * @param string $source
     * @param string $destination
     * @param boolean $skipRoot
     * @return string
     */
    public function pack($source, $destination, $skipRoot=false)
    {
        $this->_setSkipRoot($skipRoot);
        $source = realpath($source);
        $tarData = $this->_setCurrentPath($source)
        ->_setCurrentFile($source)
        ->_packToTar($skipRoot);
        $this->_writeFile($destination, $tarData);
        return $destination;
    }

    /**
     * Unpack file from TAR (Tape Archiver).
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function unpack($source, $destination)
    {
        $tempFile = $destination . DS . '~tmp-'.microtime(true).'.tar';
        $data = $this->_readFile($source);
        $this->_writeFile($tempFile, $data);
        $this->_setCurrentFile($tempFile)
        ->_setCurrentPath($tempFile)
        ->_unpackCurrentTar($destination);
        unlink($tempFile);
        return $destination;
    }

    /**
     * Extract one file from TAR (Tape Archiver).
     *
     * @param string $file
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function extract($file, $source, $destination)
    {
        $pointer = fopen($source, 'r');
        if (empty($pointer)) {
            throw new Mage_Exception('Can\'t open file: '.$source);
        }
        $list = array();
        $extractedFile = '';
        while (!feof($pointer)) {
            $header = $this->_parseHeader($pointer);
            if ($header['name'] == $file) {
                $extractedFile = $destination . basename($header['name']);
                $this->_writeFile($extractedFile, $header['data']);
                break;
            }
        }
        fclose($pointer);
        return $extractedFile;
    }
}
