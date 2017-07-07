<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

class FilesCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var \Magento\Setup\Module\I18n\FilesCollector
     */
    protected $_filesCollector;

    protected function setUp()
    {
        $this->_testDir = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/files_collector/';

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_filesCollector = $objectManagerHelper->getObject(\Magento\Setup\Module\I18n\FilesCollector::class);
    }

    public function testGetFilesWithoutMask()
    {
        $expectedResult = [$this->_testDir . 'default.xml', $this->_testDir . 'file.js'];
        $files = $this->_filesCollector->getFiles([$this->_testDir]);
        $this->assertEquals($expectedResult, $files);
    }

    public function testGetFilesWithMask()
    {
        $expectedResult = [$this->_testDir . 'file.js'];
        $this->assertEquals($expectedResult, $this->_filesCollector->getFiles([$this->_testDir], '/\.js$/'));
    }
}
