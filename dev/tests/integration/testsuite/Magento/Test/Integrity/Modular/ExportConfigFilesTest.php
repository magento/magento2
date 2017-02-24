<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\Component\ComponentRegistrar;

class ExportConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Export\Config\Reader
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $moduleDirSearch \Magento\Framework\Component\DirSearch */
        $moduleDirSearch = $objectManager->get(\Magento\Framework\Component\DirSearch::class);
        $fileIteratorFactory = $objectManager->get(\Magento\Framework\Config\FileIteratorFactory::class);
        $xmlFiles = $fileIteratorFactory->create(
            $moduleDirSearch->collectFiles(ComponentRegistrar::MODULE, 'etc/{*/export.xml,export.xml}')
        );

        $validationStateMock = $this->getMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->expects($this->any())->method('isValidationRequired')->will($this->returnValue(true));
        $fileResolverMock = $this->getMock(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($xmlFiles));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_model = $objectManager->create(
            \Magento\ImportExport\Model\Export\Config\Reader::class,
            ['fileResolver' => $fileResolverMock, 'validationState' => $validationStateMock]
        );
    }

    public function testExportXmlFiles()
    {
        $this->_model->read('global');
    }
}
