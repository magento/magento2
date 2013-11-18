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
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to create archive.
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Package;

class Writer
{

    /**
    * Name of package configuration file
    */
    const DEFAULT_NAME_PACKAGE_CONFIG = 'package.xml';

    /**
    * Temporary dir for extract DEFAULT_NAME_PACKAGE.
    */
    const PATH_TO_TEMPORARY_DIRECTORY = 'var/package/tmp/';

    /**
    * Files are used in package.
    *
    * @var array
    */
    protected $_files = array();

    /**
    * Archivator is used for extract DEFAULT_NAME_PACKAGE.
    *
    * @var \Magento\Archive
    */
    protected $_archivator = null;

    /**
    * Name of package with extension. Extension should be only one.
    * "package.tar.gz" is not ability, only "package.tgz".
    *
    * @var string
    */
    protected $_namePackage = 'package';

    /**
    * Temporary directory where package is situated.
    *
    * @var string
    */
    protected $_temporaryPackageDir = '';

    /**
    * Path to archive with package.
    *
    * @var mixed
    */
    protected $_pathToArchive = '';

    /**
    * Constructor initializes $_file.
    *
    * @param array $files
    * @param string $namePackage
    * @return \Magento\Connect\Package\Reader
    */
    public function __construct($files, $namePackage='')
    {
        $this->_files = $files;
        $this->_namePackage = $namePackage;
        return $this;
    }

    /**
    * Retrieve archivator.
    *
    * @return \Magento\Archive
    */
    protected function _getArchivator()
    {
        if (is_null($this->_archivator)) {
            $this->_archivator = new \Magento\Archive();
        }
        return $this->_archivator;
    }

    /**
    * Create dir in PATH_TO_TEMPORARY_DIRECTORY and move all files
    * to this dir.
    *
    * @return \Magento\Connect\Package\Writer
    */
    public function composePackage()
    {
        @mkdir(self::PATH_TO_TEMPORARY_DIRECTORY, 0777, true);        
        $root = self::PATH_TO_TEMPORARY_DIRECTORY . basename($this->_namePackage);
        @mkdir($root, 0777, true);
        foreach ($this->_files as $file) {
            
            if (is_dir($file) || is_file($file)) {
                $fileName = basename($file);
                $filePath = dirname($file);
                @mkdir($root . DS . $filePath, 0777, true);
                if (is_file($file)) {
                    copy($file, $root . DS . $filePath . DS . $fileName);
                } else {
                    @mkdir($root . DS . $filePath . $fileName, 0777);
                }
            }
        }
        $this->_temporaryPackageDir = $root;
        return $this;
    }

    /**
    * Add package.xml to temporary package directory.
    *
    * @param $content
    * @return \Magento\Connect\Package\Writer
    */
    public function addPackageXml($content)
    {
        file_put_contents($this->_temporaryPackageDir . DS . self::DEFAULT_NAME_PACKAGE_CONFIG, $content);
        return $this;
    }

    /**
    * Archives package.
    *
    * @return \Magento\Connect\Package\Writer
    */
    public function archivePackage()
    {
        $this->_pathToArchive = $this->_getArchivator()->pack(
            $this->_temporaryPackageDir,
            $this->_namePackage.'.tgz',
            true);

        //delete temporary dir
        \Magento\System\Dirs::rm(array("-r", $this->_temporaryPackageDir));
        return $this;
    }
    
    /**
    * Getter for pathToArchive
    *
    * @return string
    */
    public function getPathToArchive()
    {
        return $this->_pathToArchive;
    }

}
