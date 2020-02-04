<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Data\ProcessorFactory;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\MetadataConfigTypeProcessor;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class MetadataConfigTypeProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MetadataConfigTypeProcessor
     */
    protected $_model;

    /**
     * @var Initial|MockObject
     */
    protected $_initialConfigMock;

    /**
     * @var ProcessorFactory|MockObject
     */
    protected $_modelPoolMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $_backendModelMock;

    /**
     * @var ConfigSourceInterface|MockObject
     */
    private $configSourceMock;

    /**
     * @var ConfigPathResolver|MockObject
     */
    private $configPathResolverMock;

    protected function setUp()
    {
        $this->_modelPoolMock = $this->getMockBuilder(ProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_initialConfigMock = $this->getMockBuilder(Initial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_backendModelMock= $this->getMockBuilder(ProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->configSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->configPathResolverMock = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_initialConfigMock->expects($this->once())
            ->method('getMetadata')
            ->willReturn([
                'some/config/path1' => ['backendModel' => 'Custom_Backend_Model'],
                'some/config/path2' => ['backendModel' => 'Custom_Backend_Model'],
                'some/config/path3' => ['backendModel' => 'Custom_Backend_Model']
            ]);

        $this->_model = new MetadataConfigTypeProcessor(
            $this->_modelPoolMock,
            $this->_initialConfigMock,
            $this->configSourceMock,
            $this->configPathResolverMock
        );
    }

    public function testProcess()
    {
        $this->configPathResolverMock->expects($this->exactly(6))
            ->method('resolve')
            ->withConsecutive(
                ['some/config/path1', 'default'],
                ['some/config/path2', 'default'],
                ['some/config/path3', 'default'],
                ['some/config/path1', 'websites', 'website_one'],
                ['some/config/path2', 'websites', 'website_one'],
                ['some/config/path3', 'websites', 'website_one']
            )
            ->willReturnOnConsecutiveCalls(
                'default/some/config/path1',
                'default/some/config/path2',
                'default/some/config/path3',
                'websites/website_one/some/config/path1',
                'websites/website_one/some/config/path2',
                'websites/website_one/some/config/path3'
            );
        $this->configSourceMock->expects($this->exactly(6))
            ->method('get')
            ->withConsecutive(
                ['default/some/config/path1'],
                ['default/some/config/path2'],
                ['default/some/config/path3'],
                ['websites/website_one/some/config/path1'],
                ['websites/website_one/some/config/path2'],
                ['websites/website_one/some/config/path3']
            )
            ->willReturnOnConsecutiveCalls(
                'someValue',
                [],
                'someValue',
                [],
                'someValue',
                []
            );
        $this->_modelPoolMock->expects($this->exactly(3))
            ->method('get')
            ->with('Custom_Backend_Model')
            ->willReturn($this->_backendModelMock);
        $this->_backendModelMock->expects($this->exactly(3))
            ->method('processValue')
            ->withConsecutive(
                ['value2'],
                ['value1'],
                ['value3']
            )
            ->willReturnOnConsecutiveCalls(
                'default_processed_value_path2',
                'website_one_processed_value_path1',
                'website_one_processed_value_path3'
            );

        $data = [
            'default' => [
                'some' => [
                    'config' => [
                        'path1' => 'value1',
                        'path2' => 'value2',
                        'path3' => 'value3'
                    ]
                ]
            ],
            'websites' => [
                'website_one' => [
                    'some' => [
                        'config' => [
                            'path1' => 'value1',
                            'path2' => 'value2',
                            'path3' => 'value3',
                        ]
                    ]
                ]
            ]
        ];

        $expectedResult = $data;
        $expectedResult['default']['some']['config']['path2'] = 'default_processed_value_path2';
        $expectedResult['websites']['website_one']['some']['config']['path1'] = 'website_one_processed_value_path1';
        $expectedResult['websites']['website_one']['some']['config']['path3'] = 'website_one_processed_value_path3';

        $this->assertEquals($expectedResult, $this->_model->process($data));
    }
}
