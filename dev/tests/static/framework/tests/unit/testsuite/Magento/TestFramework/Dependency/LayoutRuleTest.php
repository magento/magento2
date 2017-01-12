<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

class LayoutRuleTest extends \PHPUnit_Framework_TestCase
{
    public function testNonLayoutGetDependencyInfo()
    {
        $model = new LayoutRule([], [], []);
        $content = 'any content';
        $this->assertEmpty($model->getDependencyInfo('any', 'not layout', 'any', $content));
    }

    /**
     * @param string $contents
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($contents, array $expected)
    {
        $model = new LayoutRule([], [], []);
        $this->assertEquals($expected, $model->getDependencyInfo('Magento\SomeModule', 'layout', 'any', $contents));
    }

    public function getDependencyInfoDataProvider()
    {
        return [
            [
                '<element module="Magento\AnotherModule"/>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<element module="Magento\AnotherModule"/>',
                    ]
                ],
            ],
            ['<element module="Magento\SomeModule"/>', []],
            [
                '<block class="Magento\AnotherModule\Several\Chunks"/>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_HARD,
                        'source' => '<block class="Magento\AnotherModule\Several\Chunks"/>',
                    ]
                ]
            ],
            ['<block class="Magento\SomeModule\Several\Chunks"/>', []],
            [
                '<any>
                    <extra></extra><block template="Magento_AnotherModule::template/path.phtml"/>
                </any>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<block template="Magento_AnotherModule::template/path.phtml"/>',
                    ]
                ]
            ],
            ['<block template="Magento_SomeModule::template/path.phtml"/>', []],
            [
                '<block>Magento\AnotherModule\Several\Chunks</block>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<block>Magento\AnotherModule\Several\Chunks</block>',
                    ]
                ]
            ],
            ['<block>Magento\SomeModule\Several\Chunks</block>', []],
            [
                '<template>Magento_AnotherModule::template/path.phtml</template>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<template>Magento_AnotherModule::template/path.phtml</template>',
                    ]
                ]
            ],
            ['<template>Magento_SomeModule::template/path.phtml</template>', []],
            [
                '<file>Magento_AnotherModule::file/path.txt</file>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<file>Magento_AnotherModule::file/path.txt</file>',
                    ]
                ]
            ],
            ['<file>Magento_SomeModule::file/path.txt</file>', []],
            [
                '<any helper="Magento\AnotherModule\Several\Chunks::text"/>',
                [
                    [
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<any helper="Magento\AnotherModule\Several\Chunks::text"/>',
                    ]
                ]
            ],
            ['<any helper="Magento\SomeModule\Several\Chunks::text"/>', []]
        ];
    }

    /**
     * @param string $contents
     * @param string $type
     * @dataProvider layoutGetDependencyInfoDataProvider
     */
    public function testUpdatesRouterGetDependencyInfo($contents, $type)
    {
        $model = new LayoutRule(['router_name' => ['Magento\RouterModule']], [], []);
        $this->assertEquals([], $model->getDependencyInfo('Magento\RouterModule', 'layout', 'any', $contents));
        $this->assertEquals(
            [['module' => 'Magento\RouterModule', 'type' => $type, 'source' => 'router_name_action']],
            $model->getDependencyInfo('Magento\AnotherModule', 'layout', 'any', $contents)
        );
    }

    /**
     * @param string $contents
     * @param string $type
     * @param bool $isHandle
     * @dataProvider layoutGetDependencyInfoWithReferenceDataProvider
     */
    public function testLayoutGetDependencyInfo($contents, $type, $isHandle)
    {
        // test one module
        $data = [
            'frontend' => ['any_handle_name' => ['Magento\AnyHandleModule' => 'Magento\AnyHandleModule']],
            'default' => ['singlechunk' => ['Magento\DefaultHandleModule' => 'Magento\DefaultHandleModule']],
        ];
        $model = $isHandle ? new LayoutRule([], [], $data) : new LayoutRule([], $data, []);
        $this->assertEquals(
            [],
            $model->getDependencyInfo('Magento\AnyHandleModule', 'layout', 'path/frontend/file.txt', $contents)
        );
        $this->assertEquals(
            [],
            $model->getDependencyInfo('Magento\DefaultHandleModule', 'layout', 'any', $contents)
        );
        $this->assertEquals(
            [['module' => 'Magento\DefaultHandleModule', 'type' => $type, 'source' => 'singlechunk']],
            $model->getDependencyInfo('any', 'layout', 'any', $contents)
        );
        $this->assertEquals(
            [['module' => 'Magento\AnyHandleModule', 'type' => $type, 'source' => 'any_handle_name']],
            $model->getDependencyInfo('any', 'layout', 'path/frontend/file.txt', $contents)
        );
        // test several modules
        $data = [
            'frontend' => [
                'any_handle_name' => [
                    'Magento\Theme' => 'Magento\Theme',
                    'Magento\HandleModule' => 'Magento\HandleModule',
                ],
            ],
        ];
        $model = $isHandle ? new LayoutRule([], [], $data) : new LayoutRule([], $data, []);
        $this->assertEquals(
            [['module' => 'Magento\Theme', 'type' => $type, 'source' => 'any_handle_name']],
            $model->getDependencyInfo('any', 'layout', 'path/frontend/file.txt', $contents)
        );
        $this->assertEquals(
            [],
            $model->getDependencyInfo('Magento\HandleModule', 'layout', 'path/frontend/file.txt', $contents)
        );
    }

    public function layoutGetDependencyInfoDataProvider()
    {
        return [
            [
                $this->_getLayoutFileContent('layout_handle.xml'),
                \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                true,
            ],
            [
                $this->_getLayoutFileContent('layout_handle_parent.xml'),
                \Magento\Test\Integrity\DependencyTest::TYPE_HARD,
                true
            ],
            [
                $this->_getLayoutFileContent('layout_handle_update.xml'),
                \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                true
            ]
        ];
    }

    public function layoutGetDependencyInfoWithReferenceDataProvider()
    {
        return array_merge(
            $this->layoutGetDependencyInfoDataProvider(),
            [
                [
                    $this->_getLayoutFileContent('layout_reference.xml'),
                    \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                    false,
                ]
            ]
        );
    }

    /**
     * Get content of layout file
     *
     * @param string $fileName
     * @return string
     */
    protected function _getLayoutFileContent($fileName)
    {
        return file_get_contents(str_replace('\\', '/', realpath(__DIR__)) . '/_files/' . $fileName);
    }
}
