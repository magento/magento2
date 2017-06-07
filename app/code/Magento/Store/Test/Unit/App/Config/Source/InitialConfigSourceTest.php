<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Config\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Store\App\Config\Source\InitialConfigSource;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class InitialConfigSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reader|Mock
     */
    private $readerMock;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    /**
     * @var InitialConfigSource
     */
    private $source;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->source = new InitialConfigSource(
            $this->readerMock,
            $this->deploymentConfigMock,
            'configType'
        );
    }

    public function testGet()
    {
        $path = 'path';

        $this->readerMock->expects($this->once())
            ->method('load')
            ->willReturn(['configType' => [$path => 'value']]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->assertEquals('value', $this->source->get($path));
    }

    public function testGetNotInstalled()
    {
        $path = 'path';

        $this->readerMock->expects($this->never())
            ->method('load');
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->assertEquals([], $this->source->get($path));
    }
}
