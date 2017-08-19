<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Test\Unit\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Config\Composer\Package;
use Magento\Framework\Config\Composer\PackageFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\SampleData\Model\Dependency;

class DependencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataPackagesFromComposerSuggest
     * @param string[] $moduleDirectories
     * @param callable $composerJsonGenerator
     * @param string[] $suggestionsFromLockFile
     * @param string[] $expectedPackages
     */
    public function testPackagesFromComposerSuggest(
        array $moduleDirectories,
        callable $composerJsonGenerator,
        array $suggestionsFromLockFile,
        array $expectedPackages
    ) {
        /** @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject $composerInformation */
        $composerInformation = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composerInformation->method('getSuggestedPackages')
            ->willReturn($suggestionsFromLockFile);

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();

        /** @var PackageFactory|\PHPUnit_Framework_MockObject_MockObject $packageFactory */
        $packageFactory = $this->getMockBuilder(PackageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $packageFactory->method('create')
            ->willReturnCallback(function ($args) {
                return new Package($args['json']);
            });

        /** @var ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject $componentRegistrar */
        $componentRegistrar = $this->getMockBuilder(ComponentRegistrarInterface::class)
            ->getMockForAbstractClass();
        $componentRegistrar->method('getPaths')
            ->with(ComponentRegistrar::MODULE)
            ->willReturn(
                $moduleDirectories
            );

        $directoryReadFactory = $this->getMockBuilder(Filesystem\Directory\ReadInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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
    public static function dataPackagesFromComposerSuggest()
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
                            ['path' => 'app/code/LocalModule'],
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
                            ['path' => 'app/code/LocalModuleWithoutComposerJson'],
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            ['path' => 'vendor/company/module'],
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
                            ['path' => 'vendor/company2/module/src/..'],
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
                            ['path' => 'vendor/company2/module/src'],
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            ['path' => 'vendor/company/module/..'],
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            ['path' => 'app/code/LocalModuleWithoutComposerJson/..'],
                            $test->stubFileNotFoundReader()
                        ],
                        [
                            ['path' => 'app/code/LocalModule/..'],
                            $test->stubFileNotFoundReader()
                        ],
                    ];
                },
                'suggestions' => [
                    'magento/foo-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . '100.0.0',
                    'thirdparty/bar-sample-data' => Dependency::SAMPLE_DATA_SUGGEST . '1.2.3',
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

    public function stubComposerJsonReader(array $composerJsonContent)
    {
        $stub = $this->getMockBuilder(Filesystem\Directory\ReadInterface::class)
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

    public function stubFileNotFoundReader()
    {
        $stub = $this->getMockBuilder(Filesystem\Directory\ReadInterface::class)
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
