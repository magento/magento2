<?php
/**
 * Abstract class that helps in writing tests that validate config xml files
 * are valid both individually and when merged.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

use Magento\Framework\App\Filesystem\DirectoryList;

abstract class AbstractConfigFiles extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    /**
     * @var  \Magento\Framework\Config\Reader\Filesystem
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
            $this->_fileResolverMock = $this->getMockBuilder(
                'Magento\Framework\App\Arguments\FileResolver\Primary'
            )->disableOriginalConstructor()->getMock();

            /* Enable Validation regardles of MAGE_MODE */
            $validateStateMock = $this->getMockBuilder(
                'Magento\Framework\Config\ValidationStateInterface'
            )->disableOriginalConstructor()->getMock();
            $validateStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

            $this->_reader = $this->_objectManager->create(
                $this->_getReaderClassName(),
                [
                    'configFiles' => $xmlFiles,
                    'fileResolver' => $this->_fileResolverMock,
                    'validationState' => $validateStateMock
                ]
            );

            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
            $this->_schemaFile = $filesystem->getDirectoryRead($this->getDirectoryConstant())
                ->getAbsolutePath($this->_getXsdPath());
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
        $domConfig = new \Magento\Framework\Config\Dom($file);
        $result = $domConfig->validate($this->_schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error}\n";
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
        $this->_fileResolverMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->getXmlConfigFiles()));

        try {
            // this will merge all xml files and validate them
            $this->_reader->read('global');
        } catch (\Magento\Framework\Exception $e) {
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
        $result = [];
        foreach ($fileList as $fileContent) {
            $result[] = [$fileContent];
        }
        return $result;
    }

    /**
     * Finds all config xml files based on a path glob.
     *
     * @return \Magento\Framework\Config\FileIterator
     */
    public function getXmlConfigFiles()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $directory = $objectManager->get('Magento\Framework\Filesystem')
            ->getDirectoryRead(DirectoryList::MODULES);

        return $objectManager->get('Magento\Framework\Config\FileIteratorFactory')
            ->create($directory, $directory->search($this->_getConfigFilePathGlob()));
    }

    /**
     * Returns directory (modules, library internal stc.) constant which contains XSD file
     *
     * @return string
     */
    protected function getDirectoryConstant()
    {
        return DirectoryList::MODULES;
    }

    /**
     * Returns the reader class name that will be instantiated via ObjectManager
     *
     * @return string reader class name
     */
    abstract protected function _getReaderClassName();

    /**
     * Returns a string that represents the path to the config file, starting in the app directory.
     *
     * Format is glob, so * is allowed.
     *
     * @return string
     */
    abstract protected function _getConfigFilePathGlob();

    /**
     * Returns a path to the per file XSD file, relative to the modules directory.
     *
     * @return string
     */
    abstract protected function _getXsdPath();
}
