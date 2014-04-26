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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Modular;

class ExportConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Export\Config\Reader
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $filesystem \Magento\Framework\App\Filesystem */
        $filesystem = $objectManager->get('Magento\Framework\App\Filesystem');
        $modulesDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::MODULES_DIR);
        $fileIteratorFactory = $objectManager->get('Magento\Framework\Config\FileIteratorFactory');
        $xmlFiles = $fileIteratorFactory->create(
            $modulesDirectory,
            $modulesDirectory->search('/*/*/etc/{*/export.xml,export.xml}')
        );

        $validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));
        $fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($xmlFiles));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_model = $objectManager->create(
            'Magento\ImportExport\Model\Export\Config\Reader',
            array('fileResolver' => $fileResolverMock, 'validationState' => $validationStateMock)
        );
    }

    public function testExportXmlFiles()
    {
        $this->_model->read('global');
    }
}
