<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Unit\Model\Model\Directories;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetDirectoryTreeTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var IsPathExcludedInterface|MockObject
     */
    private $isPathExcluded;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $coreConfig;

    /**
     * @var GetDirectoryTree
     */
    private $model;

    /**
     * @var array
     */
    private $foldersStruture = [
        'dir1' => [
            'dir1_1' => [

            ],
            'dir1_2' => [

            ],
            'dir1_3' => [

            ]
        ],
        'dir2' => [
            'dir2_1' => [
                'dir2_1_1' => [

                ]
            ],
            'dir2_2' => [
                'dir2_2_1' => [

                ],
                'dir2_2_2' => [

                ]
            ]
        ],
        'dir3' => [
            'dir3_1' => [
                'dir3_1_1' => [
                    'dir3_1_1_1' => [

                    ]
                ]
            ]
        ],
        'dir4' => [

        ],
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->isPathExcluded = $this->createMock(IsPathExcludedInterface::class);
        $this->coreConfig = $this->createMock(ScopeConfigInterface::class);
        $this->model = new GetDirectoryTree(
            $this->filesystem,
            $this->isPathExcluded,
            $this->coreConfig
        );
    }

    /**
     * @param array $allowedFolders
     * @param array $expected
     * @throws ValidatorException
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute(array $allowedFolders, array $expected): void
    {
        $pathsMap = $this->buildDirectoryChildrenMap($this->foldersStruture);

        $directory = $this->createMock(ReadInterface::class);
        $directory->method('isDirectory')
            ->willReturnCallback(
                function (?string $path = null) use ($pathsMap): bool {
                    if ($path === null || $path === '') {
                        return true;
                    }

                    return isset($pathsMap[$path]);
                }
            );
        $directory->method('read')
            ->willReturnCallback(
                function (?string $path = null) use ($pathsMap): array {
                    $normalizedPath = $path ?? '';
                    return $pathsMap[$normalizedPath] ?? [];
                }
            );
        $this->filesystem->method('getDirectoryRead')->willReturn($directory);
        $this->isPathExcluded->method('execute')->willReturn(false);
        $this->coreConfig->method('getValue')->willReturn($allowedFolders);
        $this->assertEquals($expected, $this->model->execute());
    }

    /**
     * @throws ValidatorException
     */
    public function testExecuteInLazyModeForRootsAndSubdirectories(): void
    {
        $pathsMap = $this->buildDirectoryChildrenMap($this->foldersStruture);
        $directory = $this->createMock(ReadInterface::class);
        $directory->method('isDirectory')
            ->willReturnCallback(
                function (?string $path = null) use ($pathsMap): bool {
                    if ($path === null || $path === '') {
                        return true;
                    }

                    return isset($pathsMap[$path]);
                }
            );
        $directory->method('read')
            ->willReturnCallback(
                function (?string $path = null) use ($pathsMap): array {
                    $normalizedPath = $path ?? '';
                    return $pathsMap[$normalizedPath] ?? [];
                }
            );
        $this->filesystem->method('getDirectoryRead')->willReturn($directory);
        $this->isPathExcluded->method('execute')->willReturn(false);
        $this->coreConfig->method('getValue')->willReturn(['dir1', 'dir2']);

        $this->assertEquals(
            [
                [
                    'text' => 'dir1',
                    'id' => 'dir1',
                    'li_attr' => ['data-id' => 'dir1'],
                    'path' => 'dir1',
                    'path_array' => ['dir1'],
                    'children' => true
                ],
                [
                    'text' => 'dir2',
                    'id' => 'dir2',
                    'li_attr' => ['data-id' => 'dir2'],
                    'path' => 'dir2',
                    'path_array' => ['dir2'],
                    'children' => true
                ],
            ],
            $this->model->execute(null, false)
        );

        $this->assertEquals(
            [
                [
                    'text' => 'dir2_2_1',
                    'id' => 'dir2/dir2_2/dir2_2_1',
                    'li_attr' => ['data-id' => 'dir2/dir2_2/dir2_2_1'],
                    'path' => 'dir2/dir2_2/dir2_2_1',
                    'path_array' => ['dir2', 'dir2_2', 'dir2_2_1'],
                    'children' => false
                ],
                [
                    'text' => 'dir2_2_2',
                    'id' => 'dir2/dir2_2/dir2_2_2',
                    'li_attr' => ['data-id' => 'dir2/dir2_2/dir2_2_2'],
                    'path' => 'dir2/dir2_2/dir2_2_2',
                    'path_array' => ['dir2', 'dir2_2', 'dir2_2_2'],
                    'children' => false
                ]
            ],
            $this->model->execute('dir2/dir2_2', false)
        );
    }

    /**
     * @throws ValidatorException
     */
    public function testExecuteInLazyModeRejectsPathOutsideAllowedRoots(): void
    {
        $pathsMap = $this->buildDirectoryChildrenMap($this->foldersStruture);
        $directory = $this->createMock(ReadInterface::class);
        $directory->method('isDirectory')
            ->willReturnCallback(
                function (?string $path = null) use ($pathsMap): bool {
                    if ($path === null || $path === '') {
                        return true;
                    }

                    return isset($pathsMap[$path]);
                }
            );
        $directory->method('read')
            ->willReturnCallback(
                function (?string $path = null) use ($pathsMap): array {
                    $normalizedPath = $path ?? '';
                    return $pathsMap[$normalizedPath] ?? [];
                }
            );
        $this->filesystem->method('getDirectoryRead')->willReturn($directory);
        $this->isPathExcluded->method('execute')->willReturn(false);
        $this->coreConfig->method('getValue')->willReturn(['dir1', 'dir2']);

        $this->assertSame([], $this->model->execute('dir3/dir3_1', false));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function executeDataProvider(): array
    {
        return [
            [
                ['dir1/dir1_1', 'dir2/dir2_2', 'dir3'],
                [
                    [
                        'text' => 'dir1_1',
                        'id' => 'dir1/dir1_1',
                        'li_attr' => ['data-id' => 'dir1/dir1_1'],
                        'path' => 'dir1/dir1_1',
                        'path_array' => ['dir1', 'dir1_1'],
                        'children' => [],
                    ],
                    [
                        'text' => 'dir2_2',
                        'id' => 'dir2/dir2_2',
                        'li_attr' => ['data-id' => 'dir2/dir2_2'],
                        'path' => 'dir2/dir2_2',
                        'path_array' => ['dir2', 'dir2_2'],
                        'children' =>
                            [
                                [
                                    'text' => 'dir2_2_1',
                                    'id' => 'dir2/dir2_2/dir2_2_1',
                                    'li_attr' =>
                                        [
                                            'data-id' => 'dir2/dir2_2/dir2_2_1',
                                        ],
                                    'path' => 'dir2/dir2_2/dir2_2_1',
                                    'path_array' => ['dir2', 'dir2_2', 'dir2_2_1'],
                                    'children' => [],
                                ],
                                [
                                    'text' => 'dir2_2_2',
                                    'id' => 'dir2/dir2_2/dir2_2_2',
                                    'li_attr' => ['data-id' => 'dir2/dir2_2/dir2_2_2'],
                                    'path' => 'dir2/dir2_2/dir2_2_2',
                                    'path_array' => ['dir2', 'dir2_2', 'dir2_2_2'],
                                    'children' => [],
                                ],
                            ],
                    ],
                    [
                        'text' => 'dir3',
                        'id' => 'dir3',
                        'li_attr' => ['data-id' => 'dir3'],
                        'path' => 'dir3',
                        'path_array' => ['dir3'],
                        'children' =>
                            [
                                [
                                    'text' => 'dir3_1',
                                    'id' => 'dir3/dir3_1',
                                    'li_attr' => ['data-id' => 'dir3/dir3_1'],
                                    'path' => 'dir3/dir3_1',
                                    'path_array' => ['dir3', 'dir3_1'],
                                    'children' =>
                                        [
                                            [
                                                'text' => 'dir3_1_1',
                                                'id' => 'dir3/dir3_1/dir3_1_1',
                                                'li_attr' => ['data-id' => 'dir3/dir3_1/dir3_1_1'],
                                                'path' => 'dir3/dir3_1/dir3_1_1',
                                                'path_array' => ['dir3', 'dir3_1', 'dir3_1_1'],
                                                'children' =>
                                                    [
                                                        [
                                                            'text' => 'dir3_1_1_1',
                                                            'id' => 'dir3/dir3_1/dir3_1_1/dir3_1_1_1',
                                                            'li_attr' => [
                                                                'data-id' => 'dir3/dir3_1/dir3_1_1/dir3_1_1_1',
                                                            ],
                                                            'path' => 'dir3/dir3_1/dir3_1_1/dir3_1_1_1',
                                                            'path_array' => [
                                                                'dir3',
                                                                'dir3_1',
                                                                'dir3_1_1',
                                                                'dir3_1_1_1',
                                                            ],
                                                            'children' => [],
                                                        ],
                                                    ],
                                            ],
                                        ],
                                ]
                            ],
                    ],
                ]

            ],
            [
                ['dir2/dir2_1', 'dir2/dir2_2'],
                [
                    [
                        'text' => 'dir2_1',
                        'id' => 'dir2/dir2_1',
                        'li_attr' => ['data-id' => 'dir2/dir2_1'],
                        'path' => 'dir2/dir2_1',
                        'path_array' => ['dir2', 'dir2_1'],
                        'children' =>
                            [
                                [
                                    'text' => 'dir2_1_1',
                                    'id' => 'dir2/dir2_1/dir2_1_1',
                                    'li_attr' =>
                                        [
                                            'data-id' => 'dir2/dir2_1/dir2_1_1',
                                        ],
                                    'path' => 'dir2/dir2_1/dir2_1_1',
                                    'path_array' => ['dir2', 'dir2_1', 'dir2_1_1'],
                                    'children' => [],
                                ]
                            ],
                    ],
                    [
                        'text' => 'dir2_2',
                        'id' => 'dir2/dir2_2',
                        'li_attr' => ['data-id' => 'dir2/dir2_2'],
                        'path' => 'dir2/dir2_2',
                        'path_array' => ['dir2', 'dir2_2'],
                        'children' =>
                            [
                                [
                                    'text' => 'dir2_2_1',
                                    'id' => 'dir2/dir2_2/dir2_2_1',
                                    'li_attr' =>
                                        [
                                            'data-id' => 'dir2/dir2_2/dir2_2_1',
                                        ],
                                    'path' => 'dir2/dir2_2/dir2_2_1',
                                    'path_array' => ['dir2', 'dir2_2', 'dir2_2_1'],
                                    'children' => [],
                                ],
                                [
                                    'text' => 'dir2_2_2',
                                    'id' => 'dir2/dir2_2/dir2_2_2',
                                    'li_attr' => ['data-id' => 'dir2/dir2_2/dir2_2_2'],
                                    'path' => 'dir2/dir2_2/dir2_2_2',
                                    'path_array' => ['dir2', 'dir2_2', 'dir2_2_2'],
                                    'children' => [],
                                ],
                            ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Build map of directory path => child directory paths.
     *
     * @param array $structure
     * @param string $parentPath
     * @return array
     */
    private function buildDirectoryChildrenMap(array $structure, string $parentPath = ''): array
    {
        $childrenMap = [$parentPath => []];
        foreach ($structure as $directoryName => $subdirectories) {
            $directoryPath = $parentPath === '' ? $directoryName : $parentPath . '/' . $directoryName;
            $childrenMap[$parentPath][] = $directoryPath;
            $childrenMap += $this->buildDirectoryChildrenMap($subdirectories, $directoryPath);
        }

        sort($childrenMap[$parentPath]);

        return $childrenMap;
    }
}
