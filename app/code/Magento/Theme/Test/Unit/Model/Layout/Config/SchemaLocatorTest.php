<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Layout\Config;

use \Magento\Theme\Model\Layout\Config\SchemaLocator;

use Magento\Framework\App\Filesystem\DirectoryList;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $object;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * Initialize testable object
     */
    public function setUp()
    {
        $path = '/root/path/lib';
        $filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')->disableOriginalConstructor()->getMock();
        $read = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')->getMock();

        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::LIB_INTERNAL)
            ->willReturn($read);
        $read->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn($path);

        $this->scheme = $path . '/Magento/Framework/View/PageLayout/etc/layouts.xsd';

        /** @var $filesystem \Magento\Framework\Filesystem */
        $this->object = new SchemaLocator($filesystem);
    }

    /**
     * cover getPerFileSchema and getSchema
     */
    public function testGetScheme()
    {
        $this->assertEquals($this->scheme, $this->object->getPerFileSchema());
        $this->assertEquals($this->scheme, $this->object->getSchema());
    }
}
