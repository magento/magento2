<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

class PhpRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PhpRule
     */
    protected $model;

    protected function setUp()
    {
        $mapRoutes = ['someModule' => ['Magento\SomeModule'], 'anotherModule' => ['Magento\OneModule']];
        $mapLayoutBlocks = ['area' => ['block.name' => ['Magento\SomeModule' => 'Magento\SomeModule']]];
        $pluginMap = [
            'Magento\Module1\Plugin1' => 'Magento\Module1\Subject',
            'Magento\Module1\Plugin2' => 'Magento\Module2\Subject',
        ];
        $this->model = new PhpRule($mapRoutes, $mapLayoutBlocks, $pluginMap);
    }

    public function testNonPhpGetDependencyInfo()
    {
        $content = 'any content';
        $this->assertEmpty($this->model->getDependencyInfo('any', 'not php', 'any', $content));
    }

    /**
     * @param string $class
     * @param string $content
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($class, $content, array $expected)
    {
        $file = $this->makeMockFilepath($class);
        $module = $this->getModuleFromClass($class);
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', $file, $content));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDependencyInfoDataProvider()
    {
        return [
            'Extend class in same module' => [
                'Magento\SomeModule\SomeClass',
                'something extends \Magento\SomeModule\Any\ClassName {',
                []
            ],
            'Extend class in different module' => [
                'Magento\AnotherModule\SomeClass',
                'something extends \Magento\SomeModule\Any\ClassName {',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName',
                    ]
                ]
            ],
            'getViewFileUrl in same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                []
            ],
            'getViewFileUrl in different module' => [
                'Magento\AnotherModule\SomeClass',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento_SomeModule',
                    ]
                ]
            ],
            'Helper class from same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->helper("Magento\SomeModule\Any\ClassName")',
                []
            ],
            'Helper class from another module' => [
                'Magento\AnotherModule\SomeClass',
                '$this->helper("Magento\SomeModule\Any\ClassName")',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName',
                    ]
                ]
            ],
            'getUrl from same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getUrl("someModule")',
                []
            ],
            'getUrl from another module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getUrl("anotherModule")',
                [
                    [
                        'module' => 'Magento\OneModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getUrl("anotherModule"',
                    ]
                ]
            ],
            'getBlock from same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getLayout()->getBlock(\'block.name\');', []
            ],
            'getBlock from another module' => [
                'Magento\AnotherModule\SomeClass',
                '$this->getLayout()->getBlock(\'block.name\');',
                [
                    [
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getBlock(\'block.name\')',
                    ]
                ]
            ],
            'Plugin on class in same module' => [
                'Magento\Module1\Plugin1',
                ', \Magento\Module1\Subject $variable',
                []
            ],
            'Plugin depends on arbitrary class in same module' => [
                'Magento\Module1\Plugin1',
                ', \Magento\Module1\NotSubject $variable',
                []
            ],
            'Plugin on class in different module' => [
                'Magento\Module1\Plugin2',
                'Magento\Module2\Subject',
                [
                    [
                        'module' => 'Magento\Module2',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\Module2\Subject',
                    ]
                ],
            ],
            'Plugin depends on arbitrary class in same module as subject' => [
                'Magento\Module1\Plugin2',
                'Magento\Module2\NotSubject',
                [
                    [
                        'module' => 'Magento\Module2',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\Module2\NotSubject',
                    ]
                ]
            ],
            'Plugin depends on arbitrary class in arbitrary module' => [
                'Magento\Module1\Plugin2',
                'Magento\OtherModule\NotSubject',
                [
                    [
                        'module' => 'Magento\OtherModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\OtherModule\NotSubject',
                    ]
                ]
            ],
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

    /**
     * Make some fake filepath to correspond to the class name
     *
     * @param string $class
     * @return string
     */
    private function makeMockFilepath($class)
    {
        return 'ClassRoot' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }

    /**
     * Get the module name like Magento\Module out of a classname, assuming for test purpose that
     * all modules are from "Magento" vendor
     *
     * @param string $class
     * @return string
     */
    private function getModuleFromClass($class)
    {
        return substr($class, 0, strpos($class, '\\', 9)); // (strlen('Magento\\') + 1) === 9
    }
}
