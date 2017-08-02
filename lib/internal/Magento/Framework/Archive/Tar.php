<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to work with tar archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

use Magento\Framework\Archive\Helper\File;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Class \Magento\Framework\Archive\Tar
 *
 * @since 2.0.0
 */
class Tar extends \Magento\Framework\Archive\AbstractArchive implements \Magento\Framework\Archive\ArchiveInterface
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
     * @since 2.0.0
     */
    protected $_currentFile;

    /**
     * Keep path to file or directory for packing.
     *
     * @var string
     * @since 2.0.0
     */
    protected $_currentPath;

    /**
     * Skip first level parent directory. Example:
     *   use test/fip.php instead test/test/fip.php;
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_skipRoot;

    /**
     * Tarball data writer
     *
     * @var File
     * @since 2.0.0
     */
    protected $_writer;

    /**
     * Tarball data reader
     *
     * @var File
     * @since 2.0.0
     */
    protected $_reader;

    /**
     * Path to file where tarball should be placed
     *
     * @var string
     * @since 2.0.0
     */
    protected $_destinationFilePath;

    /**
     * Initialize tarball writer
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initWriter()
    {
        $this->_writer = new File($this->_destinationFilePath);
        $this->_writer->open('w');

        return $this;
    }

    /**
     * Returns string that is used for tar's header parsing
     *
     * @return string
     * @since 2.0.0
     */
    protected static function _getFormatParseHeader()
    {
        return 'a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2version/' .
            'a32uname/a32gname/a8devmajor/a8devminor/a155prefix/a12closer';
    }

    /**
     * Destroy tarball writer
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _destroyWriter()
    {
        if ($this->_writer instanceof File) {
            $this->_writer->close();
            $this->_writer = null;
        }

        return $this;
    }

    /**
     * Get tarball writer
     *
     * @return File
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
     */
    protected function _initReader()
    {
        $this->_reader = new File($this->_getCurrentFile());
        $this->_reader->open('r');

        return $this;
    }

    /**
     * Destroy tarball reader
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _destroyReader()
    {
        if ($this->_reader instanceof File) {
            $this->_reader->close();
            $this->_reader = null;
        }

        return $this;
    }

    /**
     * Get tarball reader
     *
     * @return File
     * @since 2.0.0
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
     * @param bool $skipRoot
     * @return $this
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
     */
    protected function _setCurrentFile($file)
    {
        $file = str_replace('\\', '/', $file);
        $this->_currentFile = $file . (!is_link($file) && is_dir($file) && substr($file, -1) != '/' ? '/' : '');
        return $this;
    }

    /**
     * Set path to file where tarball should be placed
     *
     * @param string $destinationFilePath
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getCurrentFile()
    {
        return $this->_currentFile;
    }

    /**
     * Set path to file which is packing.
     *
     * @param string $path
     * @return $this
     * @since 2.0.0
     */
    protected function _setCurrentPath($path)
    {
        $path = str_replace('\\', '/', $path);
        if ($this->_skipRoot && is_dir($path)) {
            $this->_currentPath = $path . (substr($path, -1) != '/' ? '/' : '');
        } else {
            $this->_currentPath = dirname($path) . '/';
        }
        return $this;
    }

    /**
     * Retrieve path to file which is packing.
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getCurrentPath()
    {
        return $this->_currentPath;
    }

    /**
     * Recursively walk through file tree and create tarball
     *
     * @param bool $skipRoot
     * @param bool $finalize
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Can\'t scan dir: %1', [$file])
                );
            }

            array_shift($dirFiles);
            /* remove  './'*/
            array_shift($dirFiles);
            /* remove  '../'*/

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
     *
     * @return void
     * @since 2.0.0
     */
    protected function _packAndWriteCurrentFile()
    {
        $archiveWriter = $this->_getWriter();
        $archiveWriter->write($this->_composeHeader());

        $currentFile = $this->_getCurrentFile();

        $fileSize = 0;

        if (is_file($currentFile) && !is_link($currentFile)) {
            $fileReader = new File($currentFile);
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
     * @param bool $long
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
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
        if (!$long && strlen($nameFile) > 100) {
            $longHeader = $this->_composeHeader(true);
            $longHeader .= str_pad($nameFile, floor((strlen($nameFile) + 512 - 1) / 512) * 512, "\0");
        }
        $header = [];
        $header['100-name'] = $long ? '././@LongLink' : substr($nameFile, 0, 100);
        $header['8-mode'] = $long ? '       ' : str_pad(
            substr(sprintf("%07o", $infoFile['mode']), -4),
            6,
            '0',
            STR_PAD_LEFT
        );
        $header['8-uid'] = $long || $infoFile['uid'] == 0 ? "\0\0\0\0\0\0\0" : sprintf("%07o", $infoFile['uid']);
        $header['8-gid'] = $long || $infoFile['gid'] == 0 ? "\0\0\0\0\0\0\0" : sprintf("%07o", $infoFile['gid']);
        $header['12-size'] = $long ? sprintf(
            "%011o",
            strlen($nameFile)
        ) : sprintf(
            "%011o",
            is_dir($file) ? 0 : filesize($file)
        );
        $header['12-mtime'] = $long ? '00000000000' : sprintf("%011o", $infoFile['mtime']);
        $header['8-check'] = sprintf('% 8s', '');
        $header['1-type'] = $long ? 'L' : (is_link($file) ? 2 : (is_dir($file) ? 5 : 0));
        $header['100-symlink'] = is_link($file) ? readlink($file) : '';
        $header['6-magic'] = 'ustar ';
        $header['2-version'] = ' ';
        $a = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($file)) : ['name' => ''];
        $header['32-uname'] = $a['name'];
        $a = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($file)) : ['name' => ''];
        $header['32-gname'] = $a['name'];
        $header['8-devmajor'] = '';
        $header['8-devminor'] = '';
        $header['155-prefix'] = '';
        $header['12-closer'] = '';

        $packedHeader = '';
        foreach ($header as $key => $element) {
            $length = explode('-', $key);
            $packedHeader .= pack('a' . $length[0], $element);
        }

        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($packedHeader, $i, 1));
        }
        $packedHeader = substr_replace($packedHeader, sprintf("%07o", $checksum) . "\0", 148, 8);

        return $longHeader . $packedHeader;
    }

    /**
     * Read TAR string from file, and unpacked it.
     * Create files and directories information about described
     * in the string.
     *
     * @param string $destination path to file is unpacked
     * @return string[] list of files
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _unpackCurrentTar($destination)
    {
        $archiveReader = $this->_getReader();
        $list = [];

        while (!$archiveReader->eof()) {
            $header = $this->_extractFileHeader();

            if (!$header) {
                continue;
            }

            $currentFile = $destination . $header['name'];
            $dirname = dirname($currentFile);

            if (in_array($header['type'], ["0", chr(0), ''])) {
                if (!file_exists($dirname)) {
                    $mkdirResult = @mkdir($dirname, 0777, true);

                    if (false === $mkdirResult) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            new \Magento\Framework\Phrase('Failed to create directory %1', [$dirname])
                        );
                    }
                }

                $this->_extractAndWriteFile($header, $currentFile);
                $list[] = $currentFile;
            } elseif ($header['type'] == '5') {
                if (!file_exists($dirname)) {
                    $mkdirResult = @mkdir($currentFile, $header['mode'], true);

                    if (false === $mkdirResult) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            new \Magento\Framework\Phrase('Failed to create directory %1', [$currentFile])
                        );
                    }
                }
                $list[] = $currentFile . '/';
            } elseif ($header['type'] == '2') {
                //we do not interrupt unpack process if symlink creation failed as symlinks are not so important
                @symlink($header['symlink'], $currentFile);
            }
        }
        return $list;
    }

    /**
     * Read and decode file header information from tarball
     *
     * @return array|bool
     * @since 2.0.0
     */
    protected function _extractFileHeader()
    {
        $archiveReader = $this->_getReader();

        $headerBlock = $archiveReader->read(self::TAR_BLOCK_SIZE);

        if (strlen($headerBlock) < self::TAR_BLOCK_SIZE) {
            return false;
        }

        $header = unpack(self::_getFormatParseHeader(), $headerBlock);

        $header['mode'] = octdec($header['mode']);
        $header['uid'] = octdec($header['uid']);
        $header['gid'] = octdec($header['gid']);
        $header['size'] = octdec($header['size']);
        $header['mtime'] = octdec($header['mtime']);
        $header['checksum'] = octdec($header['checksum']);

        if ($header['type'] == "5") {
            $header['size'] = 0;
        }

        $checksum = 0;
        $headerBlock = substr_replace($headerBlock, '        ', 148, 8);

        for ($i = 0; $i < 512; $i++) {
            $checksum += ord(substr($headerBlock, $i, 1));
        }

        $checksumOk = $header['checksum'] == $checksum;
        if (isset($header['name']) && $checksumOk) {
            $header['name'] = trim($header['name']);
            if (!($header['name'] == '././@LongLink' && $header['type'] == 'L')) {
                return $header;
            }

            $realNameBlockSize = floor(
                ($header['size'] + self::TAR_BLOCK_SIZE - 1) / self::TAR_BLOCK_SIZE
            ) * self::TAR_BLOCK_SIZE;
            $realNameBlock = $archiveReader->read($realNameBlockSize);
            $realName = substr($realNameBlock, 0, $header['size']);

            $headerMain = $this->_extractFileHeader();
            $headerMain['name'] = trim($realName);
            return $headerMain;
        }

        return false;
    }

    /**
     * Extract next file from tarball by its $header information and save it to $destination
     *
     * @param array $fileHeader
     * @param string $destination
     * @return void
     * @since 2.0.0
     */
    protected function _extractAndWriteFile($fileHeader, $destination)
    {
        $fileWriter = new File($destination);
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
     * @param bool $skipRoot
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    public function pack($source, $destination, $skipRoot = false)
    {
        $this->_setSkipRoot($skipRoot);
        $source = realpath($source);
        $tarData = $this->_setCurrentPath($source)->_setDestinationFilePath($destination)->_setCurrentFile($source);

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
     * @since 2.0.0
     */
    public function unpack($source, $destination)
    {
        $this->_setCurrentFile($source)->_setCurrentPath($source);

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
     * @since 2.0.0
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

            if ($header['type'] != 5) {
                $skipBytes = floor(
                    ($header['size'] + self::TAR_BLOCK_SIZE - 1) / self::TAR_BLOCK_SIZE
                ) * self::TAR_BLOCK_SIZE;
                $archiveReader->read($skipBytes);
            }
        }

        $this->_destroyReader();
        return $extractedFile;
    }
}
