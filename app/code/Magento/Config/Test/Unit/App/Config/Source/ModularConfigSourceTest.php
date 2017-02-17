<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\ModularConfigSource;
use Magento\Framework\App\Config\Initial\Reader;

/**
 * Test config source that is retrieved from config.xml
 *
 * @package Magento\Config\Test\Unit\App\Config\Source
 */
class ModularConfigSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var ModularConfigSource
     */
    private $source;

    public function setUp()
    {
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->source = new ModularConfigSource($this->reader);
    }

    public function testGet()
    {
        $this->reader->expects($this->once())
            ->method('read')
            ->willReturn(['data' => ['path' => 'value']]);
        $this->assertEquals('value', $this->source->get('path'));
    }
}
