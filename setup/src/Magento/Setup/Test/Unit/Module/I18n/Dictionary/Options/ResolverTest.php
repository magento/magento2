<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Options;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Class ResolverTest
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $directory
     * @param bool $withContext
     * @param array $result
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions($directory, $withContext, $result)
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $componentRegistrar = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
        $root = __DIR__ . '/_files/source';
        $componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->will(
                $this->returnValueMap([
                    [ComponentRegistrar::MODULE, [$root . '/app/code/module1', $root . '/app/code/module2']],
                    [ComponentRegistrar::THEME, [$root . '/app/design']],
                ])
            );
        $directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $directoryList->expects($this->any())->method('getRoot')->willReturn('root');
        /** @var \Magento\Setup\Module\I18n\Dictionary\Options\Resolver $resolver */
        $resolver = $objectManagerHelper->getObject(
            'Magento\Setup\Module\I18n\Dictionary\Options\Resolver',
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
        $componentRegistrar = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
        $root = __DIR__ . '/_files/source';
        $componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturn([$root . '/app/code/module1', $root . '/app/code/module2']);
        $directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Setup\Module\I18n\Dictionary\Options\Resolver $resolver */
        $resolver = $objectManagerHelper->getObject(
            'Magento\Setup\Module\I18n\Dictionary\Options\Resolver',
            [
                'directory' => $directory,
                'withContext' => $withContext,
                'componentRegistrar' => $componentRegistrar,
                'directoryList' => $directoryList
            ]
        );
        $this->setExpectedException('\InvalidArgumentException', $message);
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
