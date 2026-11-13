<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Data\EnvironmentValueProcessorInterface;
use Magento\Framework\App\Config\Data\ProcessorFactory;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\MetadataConfigTypeProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetadataConfigTypeProcessorTest extends TestCase
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

    protected function setUp(): void
    {
        $this->_modelPoolMock = $this->getMockBuilder(ProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_initialConfigMock = $this->getMockBuilder(Initial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_backendModelMock= $this->getMockBuilder(ProcessorInterface::class)
            ->getMock();
        $this->configSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMock();
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
            ->willReturnCallback(
                fn ($path, $scope) => ($scope === 'default' ? 'default/' : 'websites/website_one/') . $path
            );

        $environmentData = [
            'default' => ['some' => ['config' => ['path1' => 'someValue', 'path3' => 'someValue']]],
            'websites' => ['website_one' => ['some' => ['config' => ['path2' => 'someValue']]]]
        ];
        $this->configSourceMock->expects($this->exactly(7))
            ->method('get')
            ->willReturnCallback(fn ($path = '') => $this->getEnvironmentValue($environmentData, $path));

        $this->_modelPoolMock->expects($this->exactly(6))
            ->method('get')
            ->with('Custom_Backend_Model')
            ->willReturn($this->_backendModelMock);
        $this->_backendModelMock->expects($this->exactly(3))
            ->method('processValue')
            ->willReturnCallback(fn ($value) => [
                'value2' => 'default_processed_value_path2',
                'value1' => 'website_one_processed_value_path1',
                'value3' => 'website_one_processed_value_path3'
            ][$value]);

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

    #[DataProvider('environmentValuesProvider')]
    public function testEnvironmentValuesRespectScopeInheritance(
        array $environmentData,
        $value,
        int $processValueCalls,
        array $expectedResult
    ): void {
        $data = $this->getScopeData($value);
        $model = $this->createModelForEnvironmentValues($environmentData);
        $this->_backendModelMock->expects(
            $processValueCalls === 0 ? $this->never() : $this->exactly($processValueCalls)
        )->method('processValue')
            ->willReturn('storage-processed');

        $this->assertSame($expectedResult, $model->process($data));
    }

    /**
     * @return array
     */
    public static function environmentValuesProvider(): array
    {
        $scopeData = ['some' => ['config' => ['path1' => 'env-plain']]];
        $zeroData = ['some' => ['config' => ['path1' => '0']]];
        return [
            'default environment value' => [
                ['default' => $scopeData],
                'env-plain',
                0,
                [
                    'default' => ['some' => ['config' => ['path1' => 'env-plain']]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => 'env-plain']]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => 'env-plain']]]]
                ]
            ],
            'website environment value' => [
                ['websites' => ['another_website' => $scopeData]],
                'env-plain',
                2,
                [
                    'default' => ['some' => ['config' => ['path1' => 'storage-processed']]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => 'storage-processed']]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => 'env-plain']]]]
                ]
            ],
            'matching website environment value' => [
                ['websites' => ['base' => $scopeData]],
                'env-plain',
                1,
                [
                    'default' => ['some' => ['config' => ['path1' => 'storage-processed']]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => 'env-plain']]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => 'env-plain']]]]
                ]
            ],
            'store environment value' => [
                ['stores' => ['another_store' => $scopeData]],
                'env-plain',
                3,
                [
                    'default' => ['some' => ['config' => ['path1' => 'storage-processed']]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => 'storage-processed']]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => 'storage-processed']]]]
                ]
            ],
            'matching store environment value' => [
                ['stores' => ['default' => $scopeData]],
                'env-plain',
                2,
                [
                    'default' => ['some' => ['config' => ['path1' => 'storage-processed']]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => 'storage-processed']]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => 'env-plain']]]]
                ]
            ],
            'zero string environment value' => [
                ['default' => $zeroData],
                '0',
                0,
                [
                    'default' => ['some' => ['config' => ['path1' => '0']]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => '0']]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => '0']]]]
                ]
            ],
            'scalar compared as string' => [
                ['default' => $zeroData],
                0,
                0,
                [
                    'default' => ['some' => ['config' => ['path1' => 0]]],
                    'websites' => ['base' => ['some' => ['config' => ['path1' => 0]]]],
                    'stores' => ['default' => ['some' => ['config' => ['path1' => 0]]]]
                ]
            ]
        ];
    }

    public function testInheritedEnvironmentValuesUseEnvironmentProcessor(): void
    {
        $data = $this->getScopeData('env-plain');
        $processor = $this->createMockForIntersectionOfInterfaces([
            ProcessorInterface::class,
            EnvironmentValueProcessorInterface::class
        ]);
        $model = $this->createModelForEnvironmentValues(['default' => $data['default']], $processor);
        $processor->expects($this->never())->method('processValue');
        $processor->expects($this->exactly(3))
            ->method('processEnvironmentValue')
            ->willReturnCallback(function ($value) {
                $this->assertSame('env-plain', $value);
                return 'environment-processed';
            });

        $this->assertSame($this->getScopeData('environment-processed'), $model->process($data));
    }

    public function testStoreOverrideUsesStorageProcessor(): void
    {
        $data = $this->getScopeData('env-plain');
        $model = $this->createModelForEnvironmentValues(['default' => $data['default']]);
        $data['stores']['default']['some']['config']['path1'] = 'db-cipher';
        $this->_backendModelMock->expects($this->exactly(1))
            ->method('processValue')
            ->willReturnCallback(function ($value) {
                $this->assertSame('db-cipher', $value);
                return 'storage-processed';
            });
        $expected = $data;
        $expected['stores']['default']['some']['config']['path1'] = 'storage-processed';

        $this->assertSame($expected, $model->process($data));
    }

    public function testNoEnvironmentValuesProcessPresentAndAbsentPaths(): void
    {
        $data = $this->getScopeData('db-cipher');
        $data['stores']['default'] = [];
        $model = $this->createModelForEnvironmentValues([]);
        $this->_backendModelMock->expects($this->exactly(3))
            ->method('processValue')
            ->willReturnCallback(function ($value) {
                $this->assertTrue($value === 'db-cipher' || $value === null);
                return $value === null ? 'absent-processed' : 'storage-processed';
            });
        $expected = $this->getScopeData('storage-processed');
        $expected['stores']['default']['some']['config']['path1'] = 'absent-processed';

        $this->assertSame($expected, $model->process($data));
    }

    /**
     * Build a processor for one metadata path and three scopes.
     *
     * @param array $environmentData
     * @param ProcessorInterface|null $processor
     * @return MetadataConfigTypeProcessor
     */
    private function createModelForEnvironmentValues(
        array $environmentData,
        ?ProcessorInterface $processor = null
    ): MetadataConfigTypeProcessor {
        $initialConfig = $this->createMock(Initial::class);
        $initialConfig->expects($this->exactly(1))
            ->method('getMetadata')
            ->willReturn(['some/config/path1' => ['backendModel' => 'Custom_Backend_Model']]);
        $this->configSourceMock->expects($this->exactly(4))
            ->method('get')
            ->willReturnCallback(fn ($path = '') => $this->getEnvironmentValue($environmentData, $path));
        $this->configPathResolverMock->expects($this->exactly(3))
            ->method('resolve')
            ->willReturnCallback(
                fn ($path, $scope, $scopeCode) => $scope . '/' . ($scopeCode ? $scopeCode . '/' : '') . $path
            );
        $processor = $processor ?? $this->_backendModelMock;
        $this->_modelPoolMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function ($backendModel) use ($processor) {
                $this->assertSame('Custom_Backend_Model', $backendModel);
                return $processor;
            });

        return new MetadataConfigTypeProcessor(
            $this->_modelPoolMock,
            $initialConfig,
            $this->configSourceMock,
            $this->configPathResolverMock
        );
    }

    /**
     * Look up a slash-separated path in the environment fixture.
     *
     * @param array $data
     * @param string $path
     * @return mixed
     */
    private function getEnvironmentValue(array $data, string $path)
    {
        if ($path === '') {
            return $data;
        }
        foreach (explode('/', $path) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return [];
            }
            $data = $data[$segment];
        }
        return $data;
    }

    /**
     * Build the values after scope fallback.
     *
     * @param string|int $value
     * @return array
     */
    private function getScopeData($value): array
    {
        $scopeData = ['some' => ['config' => ['path1' => $value]]];
        return [
            'default' => $scopeData,
            'websites' => ['base' => $scopeData],
            'stores' => ['default' => $scopeData]
        ];
    }
}
