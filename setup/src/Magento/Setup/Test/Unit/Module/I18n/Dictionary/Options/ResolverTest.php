<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Options;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Options\Resolver;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    /**
     * @param string $directory
     * @param bool $withContext
     * @param array $result
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions($directory, $withContext, $result)
    {
        $objectManagerHelper = new ObjectManager($this);
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $root = __DIR__ . '/_files/source';
        $componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturnMap(
                [
                    [ComponentRegistrar::MODULE, [$root . '/app/code/module1', $root . '/app/code/module2']],
                    [ComponentRegistrar::THEME, [$root . '/app/design']],
                ]
            );
        $directoryList = $this->createMock(DirectoryList::class);
        $directoryList->expects($this->any())->method('getRoot')->willReturn('root');
        /** @var Resolver $resolver */
        $resolver = $objectManagerHelper->getObject(
            Resolver::class,
            [
                'directory' => $directory,
                'withContext' => $withContext,
                'componentRegistrar' => $componentRegistrar,
                'directoryList' => $directoryList
            ]
        );
        $this->assertSame($result, $resolver->getOptions());
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        $sourceFirst = __DIR__ . '/_files/source';
        $sourceSecond = __DIR__ . '/_files/source';
        return [
            [
                $sourceFirst,
                true,
                [
                    [
                        'type' => 'php',
                        'paths' => [
                            $sourceFirst . '/app/code/module1/',
                            $sourceFirst . '/app/code/module2/',
                            $sourceFirst . '/app/design/'
                        ],
                        'fileMask' => '/\.(php|phtml)$/',
                    ],
                    [
                        'type' => 'html',
                        'paths' => [
                            $sourceFirst . '/app/code/module1/',
                            $sourceFirst . '/app/code/module2/',
                            $sourceFirst . '/app/design/'
                        ],
                        'fileMask' => '/\.html$/',
                    ],
                    [
                        'type' => 'js',
                        'paths' => [
                            $sourceFirst . '/app/code/module1/',
                            $sourceFirst . '/app/code/module2/',
                            $sourceFirst . '/app/design/',
                            $sourceFirst . '/lib/web/mage/',
                            $sourceFirst . '/lib/web/varien/',
                        ],
                        'fileMask' => '/\.(js|phtml)$/'
                    ],
                    [
                        'type' => 'xml',
                        'paths' => [
                            $sourceFirst . '/app/code/module1/',
                            $sourceFirst . '/app/code/module2/',
                            $sourceFirst . '/app/design/'
                        ],
                        'fileMask' => '/\.xml$/'
                    ]
                ],
            ],
            [
                $sourceSecond,
                false,
                [
                    ['type' => 'php', 'paths' => [$sourceSecond], 'fileMask' => '/\.(php|phtml)$/'],
                    ['type' => 'html', 'paths' => [$sourceSecond], 'fileMask' => '/\.html/'],
                    ['type' => 'js', 'paths' => [$sourceSecond], 'fileMask' => '/\.(js|phtml)$/'],
                    ['type' => 'xml', 'paths' => [$sourceSecond], 'fileMask' => '/\.xml$/']
                ]
            ],
        ];
    }

    /**
     * @param string $directory
     * @param bool $withContext
     * @param string $message
     * @dataProvider getOptionsWrongDirDataProvider
     */
    public function testGetOptionsWrongDir($directory, $withContext, $message)
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $root = __DIR__ . '/_files/source';
        $componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturn([$root . '/app/code/module1', $root . '/app/code/module2']);
        $directoryList = $this->createMock(DirectoryList::class);
        $objectManagerHelper = new ObjectManager($this);
        /** @var Resolver $resolver */
        $resolver = $objectManagerHelper->getObject(
            Resolver::class,
            [
                'directory' => $directory,
                'withContext' => $withContext,
                'componentRegistrar' => $componentRegistrar,
                'directoryList' => $directoryList
            ]
        );
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage($message);
        $resolver->getOptions();
    }

    /**
     * @return array
     */
    public function getOptionsWrongDirDataProvider()
    {
        return [
            ['not_exist', true, 'Specified path is not a Magento root directory'],
            ['not_exist', false, 'Specified path doesn\'t exist'],
        ];
    }
}
