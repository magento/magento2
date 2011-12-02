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
 * Class to work with archives
 *
 * @category    Mage
 * @package     Mage_Archive
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Archive
{

    /**
    * Archiver is used for compress.
    */
    const DEFAULT_ARCHIVER   = 'gz';

    /**
    * Default packer for directory.
    */
    const TAPE_ARCHIVER      = 'tar';

    /**
    * Current archiver is used for compress.
    *
    * @var Mage_Archiver_Tar|Mage_Archiver_Gz|Mage_Archiver_Bz
    */
    protected $_archiver=null;

    /**
    * Accessible formats for compress.
    *
    * @var array
    */
    protected $_formats = array(
        'tar'        => 'tar',
        'gz'         => 'gz',
        'gzip'       => 'gz',
        'tgz'        => 'tar.gz',
        'tgzip'      => 'tar.gz',
        'bz'         => 'bz',
        'bzip'       => 'bz',
        'bzip2'      => 'bz',
        'bz2'        => 'bz',
        'tbz'        => 'tar.bz',
        'tbzip'      => 'tar.bz',
        'tbz2'       => 'tar.bz',
        'tbzip2'     => 'tar.bz');

    /**
    * Create object of current archiver by $extension.
    *
    * @param string $extension
    * @return Mage_Archiver_Tar|Mage_Archiver_Gz|Mage_Archiver_Bz
    */
    protected function _getArchiver($extension)
    {
        if(array_key_exists(strtolower($extension), $this->_formats)) {
            $format = $this->_formats[$extension];
        } else {
            $format = self::DEFAULT_ARCHIVER;
        }
        $class = 'Mage_Archive_'.ucfirst($format);
        $this->_archiver = new $class();
        return $this->_archiver;
    }

    /**
    * Split current format to list of archivers.
    *
    * @param string $source
    * @return array
    */
    protected function _getArchivers($source)
    {
        $ext = pathinfo($source, PATHINFO_EXTENSION);
        if(!isset($this->_formats[$ext])) {
            return array();
        }
        $format = $this->_formats[$ext];
        if ($format) {
            $archivers = explode('.', $format);
            return $archivers;
        }
        return array();
    }

    /**
    * Pack file or directory to archivers are parsed from extension.
    *
    * @param string $source
    * @param string $destination
    * @param boolean $skipRoot skip first level parent
    * @return string Path to file
    */
    public function pack($source, $destination='packed.tgz', $skipRoot=false)
    {
        $archivers = $this->_getArchivers($destination);
        $interimSource = '';
        for ($i=0; $i<count($archivers); $i++ ) {
            if ($i == (count($archivers) - 1)) {
                $packed = $destination;
            } else {
                $packed = dirname($destination) . DS . '~tmp-'. microtime(true) . $archivers[$i] . '.' . $archivers[$i];
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
    * @param boolean $tillTar
    * @return string Path to file
    */
    public function unpack($source, $destination='.', $tillTar=false, $clearInterm = true)
    {
        $archivers = $this->_getArchivers($source);
        $interimSource = '';
        for ($i=count($archivers)-1; $i>=0; $i--) {
            if ($tillTar && $archivers[$i] == self::TAPE_ARCHIVER) {
                break;
            }
            if ($i == 0) {
                $packed = rtrim($destination, DS) . DS;
            } else {
                $packed = rtrim($destination, DS) . DS . '~tmp-'. microtime(true) . $archivers[$i-1] . '.' . $archivers[$i-1];
            }
            $source = $this->_getArchiver($archivers[$i])->unpack($source, $packed);
            
            //var_dump($packed, $source);
            
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
    public function extract($file, $source, $destination='.')
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
    * @param mixed $file
    * @return boolean
    */
    public function isTar($file)
    {
        $archivers = $this->_getArchivers($file);
        if (count($archivers)==1 && $archivers[0] == self::TAPE_ARCHIVER) {
            return true;
        }
        return false;
    }

}
