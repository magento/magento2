<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Config\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\DataObject;
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
     * @var DataObject|Mock
     */
    private $dataObjectMock;

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
        $this->dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->source = new InitialConfigSource(
            $this->readerMock,
            $this->deploymentConfigMock,
            'configType'
        );
    }

    /**
     * @param string $path
     * @param array $data
     * @param string|array $expected
     * @dataProvider getDataProvider
     */
    public function testGet($path, $data, $expected)
    {
        $this->readerMock->expects($this->once())
            ->method('load')
            ->willReturn($data);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->assertEquals($expected, $this->source->get($path));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            'simple path' => ['path', ['configType' => 'value'], 'value'],
            'big path' => ['path1/path2', ['configType' => 'value'], 'value'],
            'empty path' => ['', [], []],
            'null path' => [null, [], []],
            'leading path' => ['/path', [], []]
        ];
    }

    public function testGetNotInstalled()
    {
        $path = 'path';

        $this->readerMock->expects($this->never())
            ->method('load');
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->assertEquals([], $this->source->get($path));
    }
}
