<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Setup;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Module\Setup\FileResolver
     */
    private $fileResolver;

    public function setUp()
    {
        $this->directoryListMock = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->fileResolver = new FileResolver($this->directoryListMock);
    }

    public function testGetSqlSetupFiles()
    {
        $this->directoryListMock
            ->expects($this->exactly(4))
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../_files'));

        // All valid data
        $this->assertGreaterThan(0, count($this->fileResolver->getSqlSetupFiles('Magento_Module1', '*.php')));

        // Valid module name with default filename pattern
        $this->assertGreaterThan(0, count($this->fileResolver->getSqlSetupFiles('Magento_Module1')));

        // Invalid module name
        $this->assertCount(0, $this->fileResolver->getSqlSetupFiles('Magento_Module5', '*.php'));

        // Invalid filename pattern for SQL files
        $this->assertCount(0, $this->fileResolver->getSqlSetupFiles('Magento_Module1', '*.text'));
    }

    public function testGetResourceCode()
    {
        $this->directoryListMock
            ->expects($this->exactly(10))
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../_files'));

        // Valid module name with both 'data' and 'sql' directories
        $this->assertSame('module1_setup', $this->fileResolver->getResourceCode('Magento_Module1'));

        // Valid module name with only 'sql' directories (no 'data')
        $this->assertSame('module2_setup', $this->fileResolver->getResourceCode('Magento_Module2'));

        // Valid module name with only 'data' directories (no 'sql')
        $this->assertSame('module3_setup', $this->fileResolver->getResourceCode('Magento_Module3'));

        // Valid module name with no 'data' and 'sql' directories
        $this->assertNull($this->fileResolver->getResourceCode('Magento_Module4'));

        // Invalid module name
        $this->assertNull($this->fileResolver->getResourceCode('Magento_Module5'));
    }
}
