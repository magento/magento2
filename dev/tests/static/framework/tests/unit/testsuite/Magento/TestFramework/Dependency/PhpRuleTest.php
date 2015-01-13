<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

class PhpRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhpRule
     */
    protected $model;

    protected function setUp()
    {
        $mapRoutes = ['someModule' => ['Magento\SomeModule'], 'anotherModule' => ['Magento\OneModule']];
        $mapLayoutBlocks = ['area' => ['block.name' => ['Magento\SomeModule' => 'Magento\SomeModule']]];
        $this->model = new PhpRule($mapRoutes, $mapLayoutBlocks);
    }

    public function testNonPhpGetDependencyInfo()
    {
        $content = 'any content';
        $this->assertEmpty($this->model->getDependencyInfo('any', 'not php', 'any', $content));
    }

    /**
     * @param string $module
     * @param string $content
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($module, $content, array $expected)
    {
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', 'any', $content));
    }

    public function getDependencyInfoDataProvider()
    {
        return [
            ['Magento\SomeModule', 'something extends \Magento\SomeModule\Any\ClassName {', []], //1
            [
                'Magento\AnotherModule',
                'something extends \Magento\SomeModule\Any\ClassName {',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName',
                    ]
                ]
            ], // 2
            [
                'Magento\SomeModule',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                []
            ], // 3
            [
                'Magento\AnotherModule',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento_SomeModule',
                    ]
                ]
            ], //4
            ['Magento\SomeModule', '$this->helper("Magento\SomeModule\Any\ClassName")', []], //5
            [
                'Magento\AnotherModule',
                '$this->helper("Magento\SomeModule\Any\ClassName")',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName',
                    ]
                ]
            ], //6
            ['Magento\SomeModule', '$this->getUrl("someModule")', []], // 7
            [
                'Magento\AnotherModule',
                '$this->getUrl("anotherModule")',
                [
                    [
                        'module' => 'Magento\OneModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getUrl("anotherModule"',
                    ]
                ]
            ], //8
            ['Magento\SomeModule', '$this->getLayout()->getBlock(\'block.name\');', []], // 9
            [
                'Magento\AnotherModule',
                '$this->getLayout()->getBlock(\'block.name\');',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getBlock(\'block.name\')',
                    ]
                ]
            ] // 10
        ];
    }

    /**
     * @param string $module
     * @param string $content
     * @param array $expected
     * @dataProvider getDefaultModelDependencyDataProvider
     */
    public function testGetDefaultModelDependency($module, $content, array $expected)
    {
        $mapLayoutBlocks = [
            'default' => [
                'block.name' => [
                    'Magento\SomeModule' => 'Magento\SomeModule',
                ],
            ],
        ];
        $this->model = new PhpRule([], $mapLayoutBlocks);
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'template', 'any', $content));
    }

    public function getDefaultModelDependencyDataProvider()
    {
        return [
            [
                'Magento\AnotherModule',
                '$this->getLayout()->getBlock(\'block.name\');',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getBlock(\'block.name\')',
                    ]
                ],
            ]
        ];
    }
}
