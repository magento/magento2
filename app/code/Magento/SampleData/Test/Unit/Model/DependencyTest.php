<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\SampleData\Test\Unit\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Config\Composer\Package;
use Magento\Framework\Config\Composer\PackageFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Phrase;
use Magento\SampleData\Model\Dependency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Provides tests for Dependency model of SampleData module
 *
 * @covers \Magento\SampleData\Model\Dependency
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DependencyTest extends TestCase
{
    /**
     * @param string[] $moduleDirectories
     * @param callable $composerJsonGenerator
     * @param string[] $suggestionsFromLockFile
     * @param string[] $expectedPackages
     * @return void
     * @throws FileSystemException
     *
     * @dataProvider dataPackagesFromComposerSuggest
     */
    public function testPackagesFromComposerSuggest(
        array $moduleDirectories,
        callable $composerJsonGenerator,
        array $suggestionsFromLockFile,
        array $expectedPackages
    ): void {
        /** @var ComposerInformation|MockObject $composerInformation */
        $composerInformation = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composerInformation->method('getSuggestedPackages')
            ->willReturn($suggestionsFromLockFile);

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PackageFactory|MockObject $packageFactory */
        $packageFactory = $this->getMockBuilder(PackageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $packageFactory->method('create')
            ->willReturnCallback(function ($args) {
                return new Package($args['json']);
            });

        /** @var ComponentRegistrarInterface|MockObject $componentRegistrar */
        $componentRegistrar = $this->getMockBuilder(
            ComponentRegistrarInterface::class
        )->getMockForAbstractClass();
        $componentRegistrar->method('getPaths')
            ->with(ComponentRegistrar::MODULE)
            ->willReturn($moduleDirectories);

        $directoryReadFactory = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $directoryReadFactory->method('create')
            ->willReturnMap($composerJsonGenerator($this));

        $dependency = new Dependency(
            $composerInformation,
            $filesystem,
            $packageFactory,
            $componentRegistrar,
            $directoryReadFactory
        );
        $this->assertEquals($expectedPackages, $dependency->getSampleDataPackages());
    }

    /**
     * Data provider for testPackagesFromComposerSuggest
     *
     * @return array
     */
    public static function dataPackagesFromComposerSuggest(): array
    {
        return [
            [
                'moduleDirectories' => [
                    'app/code/LocalModule',
                    'app/code/LocalModuleWithoutComposerJson',
                    'vendor/company/module',
                    'vendor/company2/module/src'
                ],
                'composerJsonGenerator' => function (DependencyTest $test) {
                    return [
                        [
                            'app/code/LocalModule',
                            DriverPool::FILE,
                            $test->stubComposerJsonReader(
                                [
                                    'name' => 'local/module',
                                    'suggest' => [
                                        'local/module-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . '0.1.0'
                                    ]
                                ]
                            )
                        ],
                        [
                            'app/code/LocalModuleWithoutComposerJson',
                            DriverPool::FILE,
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            'vendor/company/module',
                            DriverPool::FILE,
                            $test->stubComposerJsonReader(
                                [
                                    'name' => 'company/module',
                                    'suggest' => [
                                        'company/module-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . '1.0.0-beta'
                                    ]
                                ]
                            )
                        ],
                        [
                            'vendor/company2/module/src/..',
                            DriverPool::FILE,
                            $test->stubComposerJsonReader(
                                [
                                    'name' => 'company2/module',
                                    'suggest' => [
                                        'company2/module-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . '1.10'
                                    ]
                                ]
                            )
                        ],
                        [
                            'vendor/company2/module/src',
                            DriverPool::FILE,
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            'vendor/company/module/..',
                            DriverPool::FILE,
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            'app/code/LocalModuleWithoutComposerJson/..',
                            DriverPool::FILE,
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            'app/code/LocalModule/..',
                            DriverPool::FILE,
                            $test->stubFileNotFoundReader()
                        ],
                    ];
                },
                'suggestionsFromLockFile' => [
                    'magento/foo-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . ' 100.0.0',
                    'thirdparty/bar-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . ' 1.2.3',
                    'thirdparty/something-else' => 'Just a suggested package',
                ],
                'expectedPackages' => [
                    'magento/foo-sample-data' => '100.0.0',
                    'thirdparty/bar-sample-data' => '1.2.3',
                    'local/module-sample-data' => '0.1.0',
                    'company/module-sample-data' => '1.0.0-beta',
                    'company2/module-sample-data' => '1.10',
                ]
            ]
        ];
    }

    /**
     * @param array $composerJsonContent
     * @return ReadInterface|MockObject
     */
    public function stubComposerJsonReader(array $composerJsonContent): MockObject
    {
        $stub = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $stub->method('isExist')
            ->with('composer.json')
            ->willReturn(true);
        $stub->method('isReadable')
            ->with('composer.json')
            ->willReturn(true);
        $stub->method('readFile')
            ->with('composer.json')
            ->willReturn(json_encode($composerJsonContent));
        return $stub;
    }

    /**
     * @return ReadInterface|MockObject
     */
    public function stubFileNotFoundReader(): MockObject
    {
        $stub = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $stub->method('isExist')
            ->with('composer.json')
            ->willReturn(false);
        $stub->method('readFile')
            ->with('composer.json')
            ->willThrowException(new FileSystemException(new Phrase('File not found')));
        return $stub;
    }
}
