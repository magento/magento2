<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Config;


use Magento\Framework\App\Config\InitialConfigSource;
use Magento\Framework\App\DeploymentConfig\Reader;

class InitialConfigSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var string
     */
    private $configType;

    /**
     * @var string
     */
    private $fileKey;

    /**
     * @var InitialConfigSource
     */
    private $source;

    public function setUp()
    {
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configType = 'configType';
        $this->fileKey = 'file.php';
        $this->source = new InitialConfigSource($this->reader, $this->configType, $this->fileKey);
    }

    public function testGet()
    {
        $path = 'path';
        $this->reader->expects($this->once())
            ->method('load')
            ->with($this->fileKey)
            ->willReturn([$this->configType => [$path => 'value']]);
        $this->assertEquals('value', $this->source->get($path));
    }
}
