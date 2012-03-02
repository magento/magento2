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
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to get package.xml from different places.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Package_Reader
{

    /**
    * Name of package file
    */
    const DEFAULT_NAME_PACKAGE = 'package.xml';

    /**
    * Temporary dir for extract DEFAULT_NAME_PACKAGE.
    */
    const PATH_TO_TEMPORARY_DIRECTORY = 'var/package/tmp/';

    /**
    * Current path to file.
    *
    * @var string
    */
    protected $_file = '';

    /**
    * Archivator is used for extract DEFAULT_NAME_PACKAGE.
    *
    * @var Mage_Archive
    */
    protected $_archivator = null;

    /**
    * Constructor initializes $_file.
    *
    * @param string $file
    * @return Mage_Connect_Package_Reader
    */
    public function __construct($file='')
    {
        if ($file) {
            $this->_file = $file;
        } else {
            $this->_file = self::DEFAULT_NAME_PACKAGE;
        }
        return $this;
    }

    /**
    * Retrieve archivator.
    *
    * @return Mage_Archive
    */
    protected function _getArchivator()
    {
        if (is_null($this->_archivator)) {
            $this->_archivator = new Mage_Archive();
        }
        return $this->_archivator;
    }

    /**
    * Open file directly or from archive and return his content.
    *
    * @return string Content of file $file
    */
    public function load()
    {
        if (!is_file($this->_file) || !is_readable($this->_file)) {
            throw new Exception('Invalid package file specified.');
        }
        if ($this->_getArchivator()->isArchive($this->_file)) {
            @mkdir(self::PATH_TO_TEMPORARY_DIRECTORY, 0777, true);
            $this->_file = $this->_getArchivator()->extract(
                self::DEFAULT_NAME_PACKAGE,
                $this->_file,
                self::PATH_TO_TEMPORARY_DIRECTORY
            );
        }
        $xmlContent = $this->_readFile();
        return $xmlContent;
    }

    /**
    * Read content file.
    *
    * @return string Content of file $file
    */
    protected function _readFile()
    {
        $handle = fopen($this->_file, 'r');
        try {
            $data = $this->_loadResource($handle);
        } catch (Mage_Exception $e) {
            fclose($handle);
            throw $e;
        }
        fclose($handle);
        return $data;
    }

    /**
    * Loads a package from specified resource
    *
    * @param resource $resource only file resources are supported at the moment
    * @return Mage_Connect_Package
    */
    protected function _loadResource(&$resource)
    {
        $data = '';
        //var_dump("====", $res, get_resource_type($resource));
        if ('stream' === get_resource_type($resource)) {
            while (!feof($resource)) {
                $data .= fread($resource, 10240);
            }
        } else {
            throw new Mage_Exception('Unsupported resource type');
        }
        return $data;
    }

}
