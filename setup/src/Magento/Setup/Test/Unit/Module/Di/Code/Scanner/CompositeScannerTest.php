<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

class CompositeScannerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\CompositeScanner
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Setup\Module\Di\Code\Scanner\CompositeScanner();
    }

    public function testScan()
    {
        $phpFiles = ['one/file/php', 'two/file/php'];
        $configFiles = ['one/file/config', 'two/file/config'];
        $files = ['php' => $phpFiles, 'config' => $configFiles];

        $scannerPhp = $this->createMock(\Magento\Setup\Module\Di\Code\Scanner\ScannerInterface::class);
        $scannerXml = $this->createMock(\Magento\Setup\Module\Di\Code\Scanner\ScannerInterface::class);

        $scannerPhpExpected = ['Model_OneProxy', 'Model_TwoFactory'];
        $scannerXmlExpected = ['Model_OneProxy', 'Model_ThreeFactory'];
        $scannerPhp->expects(
            $this->once()
        )->method(
            'collectEntities'
        )->with(
            $phpFiles
        )->will(
            $this->returnValue($scannerPhpExpected)
        );

        $scannerXml->expects(
            $this->once()
        )->method(
            'collectEntities'
        )->with(
            $configFiles
        )->will(
            $this->returnValue($scannerXmlExpected)
        );

        $this->_model->addChild($scannerPhp, 'php');
        $this->_model->addChild($scannerXml, 'config');

        $actual = $this->_model->collectEntities($files);
        $expected = [$scannerPhpExpected, $scannerXmlExpected];

        $this->assertEquals($expected, array_values($actual));
    }
}
