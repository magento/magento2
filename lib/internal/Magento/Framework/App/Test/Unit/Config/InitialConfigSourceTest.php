<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\InitialConfigSource;
use Magento\Framework\App\DeploymentConfig\Reader;

class InitialConfigSourceTest extends \PHPUnit\Framework\TestCase
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
     * @var InitialConfigSource
     */
    private $source;

    public function setUp()
    {
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configType = 'configType';
        $this->source = new InitialConfigSource($this->reader, $this->configType);
    }

    public function testGet()
    {
        $path = 'path';
        $this->reader->expects($this->once())
            ->method('load')
            ->willReturn([$this->configType => [$path => 'value']]);
        $this->assertEquals('value', $this->source->get($path));
    }
}
