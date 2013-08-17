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
 * @package     Magento_Code
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Code_Generator_Io
{
    /**
     * Default code generation directory
     * Should correspond the value from Mage_Core_Model_Dir
     */
    const DEFAULT_DIRECTORY = 'var/generation';

    /**
     * Directory permission for created directories
     */
    const DIRECTORY_PERMISSION = 0777;

    /**
     * Path to directory where new file must be created
     *
     * @var string
     */
    private $_generationDirectory;

    /**
     * @var Varien_Io_Interface
     */
    private $_ioObject;

    /**
     * Autoloader instance
     *
     * @var Magento_Autoload_IncludePath
     */
    private $_autoloader;

    /**
     * @var string
     */
    private $_directorySeparator;

    /**
     * @param Varien_Io_Interface $ioObject
     * @param Magento_Autoload_IncludePath $autoLoader
     * @param string $generationDirectory
     */
    public function __construct(Varien_Io_Interface $ioObject = null, Magento_Autoload_IncludePath $autoLoader = null,
        $generationDirectory = null
    ) {
        $this->_ioObject           = $ioObject ? : new Varien_Io_File();
        $this->_autoloader         = $autoLoader ? : new Magento_Autoload_IncludePath();
        $this->_directorySeparator = $this->_ioObject->dirsep();

        if ($generationDirectory) {
            $this->_generationDirectory
                = rtrim($generationDirectory, $this->_directorySeparator) . $this->_directorySeparator;
        } else {
            $this->_generationDirectory
                = realpath(__DIR__ . str_replace('/', $this->_directorySeparator, '/../../../../'))
                . $this->_directorySeparator . self::DEFAULT_DIRECTORY . $this->_directorySeparator;
        }
    }

    /**
     * @param string $className
     * @return string
     */
    public function getResultFileDirectory($className)
    {
        $fileName = $this->getResultFileName($className);
        $pathParts = explode($this->_directorySeparator, $fileName);
        unset($pathParts[count($pathParts) - 1]);

        return implode($this->_directorySeparator, $pathParts) . $this->_directorySeparator;
    }

    /**
     * @param string $className
     * @return string
     */
    public function getResultFileName($className)
    {
        $autoloader = $this->_autoloader;
        $resultFileName = $autoloader::getFilePath($className);
        return $this->_generationDirectory . $resultFileName;
    }

    /**
     * @param string $fileName
     * @param string $content
     * @return bool
     */
    public function writeResultFile($fileName, $content)
    {
        $content = "<?php\n" . $content;
        return $this->_ioObject->write($fileName, $content);
    }

    /**
     * @return bool
     */
    public function makeGenerationDirectory()
    {
        return $this->_makeDirectory($this->_generationDirectory);
    }

    /**
     * @param string $className
     * @return bool
     */
    public function makeResultFileDirectory($className)
    {
        return $this->_makeDirectory($this->getResultFileDirectory($className));
    }

    /**
     * @return string
     */
    public function getGenerationDirectory()
    {
        return $this->_generationDirectory;
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function fileExists($fileName)
    {
        return $this->_ioObject->fileExists($fileName, true);
    }

    /**
     * @param string $directory
     * @return bool
     */
    private function _makeDirectory($directory)
    {
        if ($this->_ioObject->isWriteable($directory)) {
            return true;
        }
        return $this->_ioObject->mkdir($directory, self::DIRECTORY_PERMISSION, true);
    }
}
