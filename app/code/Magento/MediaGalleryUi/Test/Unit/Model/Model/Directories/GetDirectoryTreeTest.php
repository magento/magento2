<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Unit\Model\Model\Directories;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree;
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
        $this->isPathExcluded = $this->getMockForAbstractClass(IsPathExcludedInterface::class);
        $this->coreConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
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
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $allowedFolders, array $expected): void
    {
        $directory = $this->getMockForAbstractClass(ReadInterface::class);
        $directory->method('isDirectory')->willReturn(true);
        $directory->method('getAbsolutePath')->willReturnArgument(0);
        $directory->method('getRelativePath')->willReturnArgument(0);
        $this->filesystem->method('getDirectoryRead')->willReturn($directory);
        $this->filesystem->method('getDirectoryReadByPath')
            ->willReturnCallback(
                function (string $path) {
                    $directory = $this->getMockBuilder(ReadInterface::class)
                        ->addMethods(['readRecursively'])
                        ->getMockForAbstractClass();
                    $directory->method('isDirectory')->willReturn(true);
                    $result = $this->foldersStruture;
                    $prefix = '';
                    foreach (explode('/', $path) as $folder) {
                        $prefix .= $folder . '/';
                        $result = $result[$folder] ?? [];
                    }
                    $directory->method('getAbsolutePath')->willReturnArgument(0);
                    $directory->method('readRecursively')->willReturn($this->flattenFoldersStructure($result, $prefix));
                    return $directory;
                }
            );
        $this->coreConfig->method('getValue')->willReturn($allowedFolders);
        $this->assertEquals($expected, $this->model->execute());
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
     * @param array $array
     * @param string $prefix
     * @return array
     */
    private function flattenFoldersStructure(array $array, string $prefix = ''): array
    {
        $paths = [];
        foreach ($array as $key => $value) {
            $path = $prefix . $key;
            $paths[] = [$path];
            $paths[] = $this->flattenFoldersStructure($value, $path . '/');
        }
        return array_merge(...$paths);
    }
}
