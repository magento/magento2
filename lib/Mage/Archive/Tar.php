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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
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
     * Tar block size
     *
     * @const int
     */
    const TAR_BLOCK_SIZE = 512;

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
    * Tarball data writer
    *
    * @var Mage_Archive_Helper_File
    */
    protected $_writer;

    /**
    * Tarball data reader
    *
    * @var Mage_Archive_Helper_File
    */
    protected $_reader;

    /**
    * Path to file where tarball should be placed
    *
    * @var string
    */
    protected $_destinationFilePath;

    /**
     * Initialize tarball writer
     *
     * @return Mage_Archive_Tar
     */
    protected function _initWriter()
    {
        $this->_writer = new Mage_Archive_Helper_File($this->_destinationFilePath);
        $this->_writer->open('w');

        return $this;
    }

    /**
     * Returns string that is used for tar's header parsing
     *
     * @return string
     */
    protected static final function _getFormatParseHeader()
    {
        return 'a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2version/'
            . 'a32uname/a32gname/a8devmajor/a8devminor/a155prefix/a12closer';
    }

    /**
     * Destroy tarball writer
     *
     * @return Mage_Archive_Tar
     */
    protected function _destroyWriter()
    {
        if ($this->_writer instanceof Mage_Archive_Helper_File) {
            $this->_writer->close();
            $this->_writer = null;
        }

        return $this;
    }

    /**
     * Get tarball writer
     *
     * @return Mage_Archive_Helper_File
     */
    protected function _getWriter()
    {
        if (!$this->_writer) {
            $this->_initWriter();
        }

        return $this->_writer;
    }

    /**
     * Initialize tarball reader
     *
     * @return Mage_Archive_Tar
     */
    protected function _initReader()
    {
        $this->_reader = new Mage_Archive_Helper_File($this->_getCurrentFile());
        $this->_reader->open('r');

        return $this;
    }

    /**
     * Destroy tarball reader
     *
     * @return Mage_Archive_Tar
     */
    protected function _destroyReader()
    {
        if ($this->_reader instanceof Mage_Archive_Helper_File) {
            $this->_reader->close();
            $this->_reader = null;
        }

        return $this;
    }

    /**
     * Get tarball reader
     *
     * @return Mage_Archive_Helper_File
     */
    protected function _getReader()
    {
        if (!$this->_reader) {
            $this->_initReader();
        }

        return $this->_reader;
    }

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
        $this->_currentFile = $file .((!is_link($file) && is_dir($file) && substr($file, -1) != DS) ? DS : '');
        return $this;
    }

    /**
    * Set path to file where tarball should be placed
    *
    * @param string $destinationFilePath
    * @return Mage_Archive_Tar
    */
    protected function _setDestinationFilePath($destinationFilePath)
    {
        $this->_destinationFilePath = $destinationFilePath;
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
     * @deprecated after 1.7.0.0
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
     * Recursively walk through file tree and create tarball
     *
     * @param boolean $skipRoot
     * @param boolean $finalize
     * @throws Mage_Exception
     */
    protected function _createTar($skipRoot = false, $finalize = false)
    {
        if (!$skipRoot) {
            $this->_packAndWriteCurrentFile();
        }

        $file = $this->_getCurrentFile();

        if (is_dir($file)) {
            $dirFiles = scandir($file);

            if (false === $dirFiles) {
                throw new Mage_Exception('Can\'t scan dir: ' . $file);
            }

            array_shift($dirFiles); /* remove  './'*/
            array_shift($dirFiles); /* remove  '../'*/

            foreach ($dirFiles as $item) {
                $this->_setCurrentFile($file . $item)->_createTar();
            }
        }

        if ($finalize) {
            $this->_getWriter()->write(str_repeat("\0", self::TAR_BLOCK_SIZE * 12));
        }
    }

    /**
     * Write current file to tarball
     */
    protected function _packAndWriteCurrentFile()
    {
        $archiveWriter = $this->_getWriter();
        $archiveWriter->write($this->_composeHeader());

        $currentFile = $this->_getCurrentFile();

        $fileSize = 0;

        if (is_file($currentFile) && !is_link($currentFile)) {
            $fileReader = new Mage_Archive_Helper_File($currentFile);
            $fileReader->open('r');

            while (!$fileReader->eof()) {
                $archiveWriter->write($fileReader->read());
            }

            $fileReader->close();

            $fileSize = filesize($currentFile);
        }

        $appendZerosCount = (self::TAR_BLOCK_SIZE - $fileSize % self::TAR_BLOCK_SIZE) % self::TAR_BLOCK_SIZE;
        $archiveWriter->write(str_repeat("\0", $appendZerosCount));
    }

    /**
     * Compose header for current file in TAR format.
     * If length of file's name greater 100 characters,
     * method breaks header into two pieces. First contains
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
        $header['8-mode']         = $long ? '       '
            : str_pad(substr(sprintf("%07o", $infoFile['mode']),-4), 6, '0', STR_PAD_LEFT);
        $header['8-uid']          = $long || $infoFile['uid']==0?"\0\0\0\0\0\0\0":sprintf("%07o", $infoFile['uid']);
        $header['8-gid']          = $long || $infoFile['gid']==0?"\0\0\0\0\0\0\0":sprintf("%07o", $infoFile['gid']);
        $header['12-size']        = $long ? sprintf("%011o", strlen($nameFile)) : sprintf("%011o", is_dir($file)
            ? 0 : filesize($file));
        $header['12-mtime']       = $long?'00000000000':sprintf("%011o", $infoFile['mtime']);
        $header['8-check']        = sprintf('% 8s', '');
        $header['1-type']         = $long ? 'L' : (is_link($file) ? 2 : (is_dir($file) ? 5 : 0));
        $header['100-symlink']    = is_link($file) ? readlink($file) : '';
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
     * @throws Mage_Exception
     */
    protected function _unpackCurrentTar($destination)
    {
        $archiveReader = $this->_getReader();
        $list = array();

        while (!$archiveReader->eof()) {
            $header = $this->_extractFileHeader();

            if (!$header) {
                continue;
            }

            $currentFile = $destination . $header['name'];
            $dirname = dirname($currentFile);

            if (in_array($header['type'], array("0",chr(0), ''))) {

                if(!file_exists($dirname)) {
                    $mkdirResult = @mkdir($dirname, 0777, true);

                    if (false === $mkdirResult) {
                        throw new Mage_Exception('Failed to create directory ' . $dirname);
                    }
                }

                $this->_extractAndWriteFile($header, $currentFile);
                $list[] = $currentFile;

            } elseif ($header['type'] == '5') {

                if(!file_exists($dirname)) {
                    $mkdirResult = @mkdir($currentFile, $header['mode'], true);

                    if (false === $mkdirResult) {
                        throw new Mage_Exception('Failed to create directory ' . $currentFile);
                    }
                }
                $list[] = $currentFile . DS;
            } elseif ($header['type'] == '2') {

                $symlinkResult = @symlink($header['symlink'], $currentFile);

                if (false === $symlinkResult) {
                    throw new Mage_Exception('Failed to create symlink ' . $currentFile . ' to ' . $header['symlink']);
                }
            }
        }

        return $list;
    }

    /**
     * Get header from TAR string and unpacked it by format.
     *
     * @deprecated after 1.7.0.0
     * @param resource $pointer
     * @return string
     */
    protected function _parseHeader(&$pointer)
    {
        $firstLine = fread($pointer, 512);

        if (strlen($firstLine)<512){
            return false;
        }

        $fmt = self::_getFormatParseHeader();
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
     * Read and decode file header information from tarball
     *
     * @return array|boolean
     */
    protected function _extractFileHeader()
    {
        $archiveReader = $this->_getReader();

        $headerBlock = $archiveReader->read(self::TAR_BLOCK_SIZE);

        if (strlen($headerBlock) < self::TAR_BLOCK_SIZE) {
            return false;
        }

        $header = unpack(self::_getFormatParseHeader(), $headerBlock);

        $header['mode']     = octdec($header['mode']);
        $header['uid']      = octdec($header['uid']);
        $header['gid']      = octdec($header['gid']);
        $header['size']     = octdec($header['size']);
        $header['mtime']    = octdec($header['mtime']);
        $header['checksum'] = octdec($header['checksum']);

        if ($header['type'] == "5") {
            $header['size'] = 0;
        }

        $checksum = 0;
        $headerBlock = substr_replace($headerBlock, '        ', 148, 8);

        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($headerBlock, $i, 1));
        }

        $isUstar = 'ustar' == strtolower(substr($header['magic'], 0, 5));

        $checksumOk = $header['checksum'] == $checksum;
        if (isset($header['name']) && $checksumOk) {

            if (!($header['name'] == '././@LongLink' && $header['type'] == 'L')) {
                return $header;
            }

            $realNameBlockSize = floor(($header['size'] + self::TAR_BLOCK_SIZE - 1) / self::TAR_BLOCK_SIZE)
                * self::TAR_BLOCK_SIZE;
            $realNameBlock = $archiveReader->read($realNameBlockSize);
            $realName = substr($realNameBlock, 0, $header['size']);

            $headerMain = $this->_extractFileHeader();
            $headerMain['name'] = $realName;
            return $headerMain;
        }

        return false;
    }

    /**
     * Extract next file from tarball by its $header information and save it to $destination
     *
     * @param array $fileHeader
     * @param string $destination
     */
    protected function _extractAndWriteFile($fileHeader, $destination)
    {
        $fileWriter = new Mage_Archive_Helper_File($destination);
        $fileWriter->open('w', $fileHeader['mode']);

        $archiveReader = $this->_getReader();

        $filesize = $fileHeader['size'];
        $bytesExtracted = 0;

        while ($filesize > $bytesExtracted && !$archiveReader->eof()) {
            $block = $archiveReader->read(self::TAR_BLOCK_SIZE);
            $nonExtractedBytesCount = $filesize - $bytesExtracted;

            $data = substr($block, 0, $nonExtractedBytesCount);
            $fileWriter->write($data);

            $bytesExtracted += strlen($block);
        }
    }

    /**
     * Pack file to TAR (Tape Archiver).
     *
     * @param string $source
     * @param string $destination
     * @param boolean $skipRoot
     * @return string
     */
    public function pack($source, $destination, $skipRoot = false)
    {
        $this->_setSkipRoot($skipRoot);
        $source = realpath($source);
        $tarData = $this->_setCurrentPath($source)
            ->_setDestinationFilePath($destination)
            ->_setCurrentFile($source);

        $this->_initWriter();
        $this->_createTar($skipRoot, true);
        $this->_destroyWriter();

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
        $this->_setCurrentFile($source)
            ->_setCurrentPath($source);

        $this->_initReader();
        $this->_unpackCurrentTar($destination);
        $this->_destroyReader();

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
        $this->_setCurrentFile($source);
        $this->_initReader();

        $archiveReader = $this->_getReader();
        $extractedFile = '';

        while (!$archiveReader->eof()) {
            $header = $this->_extractFileHeader();
            if ($header['name'] == $file) {
                $extractedFile = $destination . basename($header['name']);
                $this->_extractAndWriteFile($header, $extractedFile);
                break;
            }

            if ($header['type'] != 5){
                $skipBytes = floor(($header['size'] + self::TAR_BLOCK_SIZE - 1) / self::TAR_BLOCK_SIZE)
                    * self::TAR_BLOCK_SIZE;
                $archiveReader->read($skipBytes);
            }
        }

        $this->_destroyReader();
        return $extractedFile;
    }
}
