<?php
/**
 * Abstract class that helps in writing tests that validate config xml files
 * are valid both individually and when merged.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\TestCase;

abstract class AbstractConfigFiles extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    /**
     * @var  \Magento\Config\Reader\Filesystem
     */
    protected $_reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $xmlFiles = $this->getXmlConfigFiles();
        if (!empty($xmlFiles)) {

            $this->_fileResolverMock = $this->getMockBuilder('Magento\Core\Model\Config\FileResolver\Primary')
                ->disableOriginalConstructor()->getMock();

            /* Enable Validation regardles of MAGE_MODE */
            $validateStateMock = $this->getMockBuilder('Magento\Config\ValidationStateInterface')
                ->disableOriginalConstructor()->getMock();
            $validateStateMock->expects($this->any())
                ->method('isValidated')
                ->will($this->returnValue(true));

            $this->_reader = $this->_objectManager->create($this->_getReaderClassName(),
                array(
                    'configFiles' => $xmlFiles,
                    'fileResolver' => $this->_fileResolverMock,
                    'validationState' => $validateStateMock
                )
            );

            $dirs = $this->_objectManager->get('Magento\App\Dir');
            $modulesDir = $dirs->getDir(\Magento\App\Dir::MODULES);
            $this->_schemaFile = $modulesDir . $this->_getXsdPath();
        }
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance($this->_getReaderClassName());
    }

    /**
     * @dataProvider xmlConfigFileProvider
     */
    public function testXmlConfigFile($file, $skip = false)
    {
        if ($skip) {
            $this->markTestSkipped('There are no xml files in the system for this test.');
        }
        $domConfig = new \Magento\Config\Dom(file_get_contents($file));
        $result = $domConfig->validate($this->_schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "$error\n";
        }

        $this->assertTrue($result, $message);
    }

    public function testMergedConfig()
    {
        $files = $this->getXmlConfigFiles();
        if (empty($files)) {
            $this->markTestSkipped('There are no xml files in the system for this test.');
        }
        // have the file resolver return all relevant xml files
        $this->_fileResolverMock->expects($this->once())->method('get')
            ->will($this->returnValue($this->getXmlConfigFiles()));
        try {
            // this will merge all xml files and validate them
            $this->_reader->read('global');
        } catch (\Magento\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Returns an array of all the config xml files for this test.
     *
     * Handles the case where no files were found and notifies the test to skip.
     * This is needed to avoid a fatal error caused by a provider returning an empty array.
     *
     * @return array
     */
    public function xmlConfigFileProvider()
    {
        $fileList = $this->getXmlConfigFiles();
        if (empty($fileList)) {
            return array(array(false, true));
        }

        $dataProviderResult = array();
        foreach ($fileList as $file) {
            $dataProviderResult[$file] = array($file);
        }
        return $dataProviderResult;
    }

    /**
     * Finds all config xml files based on a path glob.
     *
     * @return array
     */
    public function getXmlConfigFiles()
    {
        return glob(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir')->getDir('app')
                . $this->_getConfigFilePathGlob()
        );
    }

    /**
     * Returns the reader class name that will be instantiated via ObjectManager
     *
     * @return string reader class name
     */
    protected abstract function _getReaderClassName();

    /**
     * Returns a string that represents the path to the config file, starting in the app directory.
     *
     * Format is glob, so * is allowed.
     *
     * @return string
     */
    protected abstract function _getConfigFilePathGlob();

    /**
     * Returns a path to the per file XSD file, relative to the modules directory.
     *
     * @return string
     */
    protected abstract function _getXsdPath();
}
