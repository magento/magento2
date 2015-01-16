<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Config;

use Magento\Framework\Filesystem;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read | \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDirectoryRead;

    /**
     * @var FileResolver
     */
    private $fileResolver;

    public function setUp()
    {
        $this->mockDirectoryRead = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Read')
            ->disableOriginalConstructor()
            ->getMock();
        $stubFileIteratorFactory = $this->getMockBuilder('Magento\Framework\Config\FileIteratorFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $stubFilesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $stubFilesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($this->mockDirectoryRead);
        $this->fileResolver = new FileResolver($stubFilesystem, $stubFileIteratorFactory);
    }

    public function testItAppliesTheFilenamePattern()
    {
        $this->mockDirectoryRead->expects($this->once())
            ->method('search')
            ->with($this->matchesRegularExpression('#\*\.xml$#'))
            ->willReturn([]);

        $this->fileResolver->get('*.xml', '');
    }
}
