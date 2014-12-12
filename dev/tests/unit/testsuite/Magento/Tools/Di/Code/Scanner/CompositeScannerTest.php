<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Di\Code\Scanner;

class CompositeScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\Code\Scanner\CompositeScanner
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Di\Code\Scanner\CompositeScanner();
    }

    public function testScan()
    {
        $phpFiles = ['one/file/php', 'two/file/php'];
        $configFiles = ['one/file/config', 'two/file/config'];
        $files = ['php' => $phpFiles, 'config' => $configFiles];

        $scannerPhp = $this->getMock('Magento\Tools\Di\Code\Scanner\ScannerInterface');
        $scannerXml = $this->getMock('Magento\Tools\Di\Code\Scanner\ScannerInterface');

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
