<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;

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
        $this->directoryListMock = $this->getMockBuilder('Magento\Framework\App\Filesystem\DirectoryList')
            ->disableOriginalConstructor()
            ->setMethods(['getPath'])
            ->getMock();

        $this->fileResolver = new FileResolver($this->directoryListMock);
    }

    public function testGetSqlSetupFiles()
    {
        $this->directoryListMock
            ->expects($this->any())
            ->method('getPath')
            ->with(DirectoryList::MODULES)
            ->will($this->returnValue(BP . '/app/code'));

        // All valid data
        $this->assertGreaterThan(0, count($this->fileResolver->getSqlSetupFiles('Magento_Core', '*.php')));

        // Valid module name with default filename pattern
        $this->assertGreaterThan(0, count($this->fileResolver->getSqlSetupFiles('Magento_Core')));

        // Invalid module name
        $this->assertCount(0, $this->fileResolver->getSqlSetupFiles('Magento_NonCore', '*.php'));

        // Invalid filename pattern for SQL files
        $this->assertCount(0, $this->fileResolver->getSqlSetupFiles('Magento_NonCore', '*.text'));
    }

    public function testGetResourceCode()
    {
        $this->directoryListMock
            ->expects($this->any())
            ->method('getPath')
            ->with(DirectoryList::MODULES)
            ->will($this->returnValue(BP . '/app/code'));

        // Valid module name with both 'data' and 'sql' directories
        $this->assertSame('core_setup', $this->fileResolver->getResourceCode('Magento_Core'));

        // Valid module name with only 'sql' directories (no 'data')
        $this->assertSame('adminnotification_setup', $this->fileResolver->getResourceCode('Magento_AdminNotification'));

        // Valid module name with only 'data' directories (no 'sql')
        $this->assertSame('checkout_setup', $this->fileResolver->getResourceCode('Magento_Checkout'));

        // Valid module name with no 'data' and 'sql' directories
        $this->assertNull($this->fileResolver->getResourceCode('Magento_Backend'));

        // Invalid module name
        $this->assertNull($this->fileResolver->getResourceCode('Magento_SomeModule'));
    }
}
