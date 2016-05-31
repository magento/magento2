<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\Archive\Bz;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Archive\Tar;

/**
 * Class to work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Archive
{
    /**
     * Archiver is used for compress.
     */
    const DEFAULT_ARCHIVER = 'gz';

    /**
     * Default packer for directory.
     */
    const TAPE_ARCHIVER = 'tar';

    /**
     * Current archiver is used for compress.
     *
     * @var \Magento\Framework\Archive\Tar|\Magento\Framework\Archive\Gz|\Magento\Framework\Archive\Bz
     */
    protected $_archiver = null;

    /**
     * Accessible formats for compress.
     *
     * @var array
     */
    protected $_formats = [
        'tar' => 'tar',
        'gz' => 'gz',
        'gzip' => 'gz',
        'tgz' => 'tar.gz',
        'tgzip' => 'tar.gz',
        'bz' => 'bz',
        'bzip' => 'bz',
        'bzip2' => 'bz',
        'bz2' => 'bz',
        'tbz' => 'tar.bz',
        'tbzip' => 'tar.bz',
        'tbz2' => 'tar.bz',
        'tbzip2' => 'tar.bz',
    ];

    /**
     * Create object of current archiver by $extension.
     *
     * @param string $extension
     * @return Tar|Gz|Bz
     */
    protected function _getArchiver($extension)
    {
        $extension = strtolower($extension);
        $format = isset($this->_formats[$extension]) ? $this->_formats[$extension] : self::DEFAULT_ARCHIVER;
        $class = '\\Magento\Framework\Archive\\' . ucfirst($format);
        $this->_archiver = new $class();
        return $this->_archiver;
    }

    /**
     * Split current format to list of archivers.
     *
     * @param string $source
     * @return string[]|string
     */
    protected function _getArchivers($source)
    {
        $ext = pathinfo($source, PATHINFO_EXTENSION);
        if (!empty($this->_formats[$ext])) {
            return explode('.', $this->_formats[$ext]);
        }
        return [];
    }

    /**
     * Pack file or directory to archivers are parsed from extension.
     *
     * @param string $source
     * @param string $destination
     * @param boolean $skipRoot skip first level parent
     * @return string Path to file
     */
    public function pack($source, $destination = 'packed.tgz', $skipRoot = false)
    {
        $archivers = $this->_getArchivers($destination);
        $interimSource = '';
        for ($i = 0; $i < count($archivers); $i++) {
            if ($i == count($archivers) - 1) {
                $packed = $destination;
            } else {
                $packed = dirname($destination) . '/~tmp-' . microtime(true) . $archivers[$i] . '.' . $archivers[$i];
            }
            $source = $this->_getArchiver($archivers[$i])->pack($source, $packed, $skipRoot);
            if ($interimSource && $i < count($archivers)) {
                unlink($interimSource);
            }
            $interimSource = $source;
        }
        return $source;
    }

    /**
     * Unpack file from archivers are parsed from extension.
     * If $tillTar == true unpack file from archivers till
     * meet TAR archiver.
     *
     * @param string $source
     * @param string $destination
     * @param bool $tillTar
     * @param bool $clearInterm
     * @return string Path to file
     */
    public function unpack($source, $destination = '.', $tillTar = false, $clearInterm = true)
    {
        $archivers = $this->_getArchivers($source);
        $interimSource = '';
        for ($i = count($archivers) - 1; $i >= 0; $i--) {
            if ($tillTar && $archivers[$i] == self::TAPE_ARCHIVER) {
                break;
            }
            if ($i == 0) {
                $packed = rtrim($destination, '/') . '/';
            } else {
                $packed = rtrim(
                    $destination,
                    '/'
                ) . '/~tmp-' . microtime(
                    true
                ) . $archivers[$i - 1] . '.' . $archivers[$i - 1];
            }
            $source = $this->_getArchiver($archivers[$i])->unpack($source, $packed);

            if ($clearInterm && $interimSource && $i >= 0) {
                unlink($interimSource);
            }
            $interimSource = $source;
        }
        return $source;
    }

    /**
     * Extract one file from TAR (Tape Archiver).
     *
     * @param string $file
     * @param string $source
     * @param string $destination
     * @return string Path to file
     */
    public function extract($file, $source, $destination = '.')
    {
        $tarFile = $this->unpack($source, $destination, true);
        $resFile = $this->_getArchiver(self::TAPE_ARCHIVER)->extract($file, $tarFile, $destination);
        if (!$this->isTar($source)) {
            unlink($tarFile);
        }
        return $resFile;
    }

    /**
     * Check file is archive.
     *
     * @param string $file
     * @return boolean
     */
    public function isArchive($file)
    {
        $archivers = $this->_getArchivers($file);
        if (count($archivers)) {
            return true;
        }
        return false;
    }

    /**
     * Check file is TAR.
     *
     * @param string $file
     * @return boolean
     */
    public function isTar($file)
    {
        $archivers = $this->_getArchivers($file);
        if (count($archivers) == 1 && $archivers[0] == self::TAPE_ARCHIVER) {
            return true;
        }
        return false;
    }
}
