<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Dependency;

use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\TestFramework\Dependency\Reader\ClassScanner;
use Magento\TestFramework\Dependency\Route\RouteMapper;

/**
 * Test for PhpRule dependency check
 */
class PhpRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PhpRule
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ClassScanner
     */
    private $classScanner;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | Filesystem
     */
    private $webApiConfigReader;

    private $pluginMap;
    private $mapRoutes;
    private $mapLayoutBlocks;
    private $whitelist;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->mapRoutes = ['someModule' => ['Magento\SomeModule'], 'anotherModule' => ['Magento\OneModule']];
        $this->mapLayoutBlocks = ['area' => ['block.name' => ['Magento\SomeModule' => 'Magento\SomeModule']]];
        $this->pluginMap = [
            'Magento\Module1\Plugin1' => 'Magento\Module1\Subject',
            'Magento\Module1\Plugin2' => 'Magento\Module2\Subject',
        ];
        $this->whitelist = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->classScanner = $this->createMock(ClassScanner::class);
        $this->webApiConfigReader = $this->makeWebApiConfigReaderMock();

        $this->model = new PhpRule(
            $this->mapRoutes,
            $this->mapLayoutBlocks,
            $this->webApiConfigReader,
            $this->pluginMap,
            $this->whitelist,
            $this->classScanner
        );
    }

    /**
     * @throws \Exception
     */
    public function testNonPhpGetDependencyInfo()
    {
        $content = 'any content';
        $this->assertEmpty($this->model->getDependencyInfo('any', 'not php', 'any', $content));
    }

    /**
     * @param string $class
     * @param string $content
     * @param int $expectedScans
     * @param array $expected
     *
     * @throws \Exception
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo(string $class, string $content, int $expectedScans, array $expected): void
    {
        $file = $this->makeMockFilepath($class);
        $module = $this->getModuleFromClass($class);
        $this->classScanner->expects($this->exactly($expectedScans))
            ->method('getClassName')
            ->with($file)
            ->willReturn($class);
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', $file, $content));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getDependencyInfoDataProvider()
    {
        return [
            'Extend class in same module' => [
                'Magento\SomeModule\SomeClass',
                'something extends \Magento\SomeModule\Any\ClassName {',
                0,
                []
            ],
            'Extend class in different module' => [
                'Magento\AnotherModule\SomeClass',
                'something extends \Magento\SomeModule\Any\ClassName {',
                1,
                [
                    [
                        'modules' => ['Magento\SomeModule'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName',
                    ]
                ]
            ],
            'getViewFileUrl in same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                0,
                []
            ],
            'getViewFileUrl in different module' => [
                'Magento\AnotherModule\SomeClass',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                1,
                [
                    [
                        'modules' => ['Magento\SomeModule'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento_SomeModule',
                    ]
                ]
            ],
            'Helper class from same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->helper("Magento\SomeModule\Any\ClassName")',
                0,
                []
            ],
            'Helper class from another module' => [
                'Magento\AnotherModule\SomeClass',
                '$this->helper("Magento\SomeModule\Any\ClassName")',
                1,
                [
                    [
                        'modules' => ['Magento\SomeModule'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName',
                    ]
                ]
            ],
            'getBlock from same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getLayout()->getBlock(\'block.name\');',
                0,
                []
            ],
            'getBlock from another module' => [
                'Magento\AnotherModule\SomeClass',
                '$this->getLayout()->getBlock(\'block.name\');',
                0,
                [
                    [
                        'modules' => ['Magento\SomeModule'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'getBlock(\'block.name\')',
                    ]
                ]
            ],
            'Plugin on class in same module' => [
                'Magento\Module1\Plugin1',
                ', \Magento\Module1\Subject $variable',
                0,
                []
            ],
            'Plugin depends on arbitrary class in same module' => [
                'Magento\Module1\Plugin1',
                ', \Magento\Module1\NotSubject $variable',
                0,
                []
            ],
            'Plugin on class in different module' => [
                'Magento\Module1\Plugin2',
                'Magento\Module2\Subject',
                1,
                [
                    [
                        'modules' => ['Magento\Module2'],
                        'type' => RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\Module2\Subject',
                    ]
                ],
            ],
            'Plugin depends on arbitrary class in same module as subject' => [
                'Magento\Module1\Plugin2',
                'Magento\Module2\NotSubject',
                1,
                [
                    [
                        'modules' => ['Magento\Module2'],
                        'type' => RuleInterface::TYPE_SOFT,
                        'source' => 'Magento\Module2\NotSubject',
                    ]
                ]
            ],
            'Plugin depends on arbitrary class in arbitrary module' => [
                'Magento\Module1\Plugin2',
                'Magento\OtherModule\NotSubject',
                1,
                [
                    [
                        'modules' => ['Magento\OtherModule'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'Magento\OtherModule\NotSubject',
                    ]
                ]
            ],
        ];
    }

    /**
     * @param string $class
     * @param string $content
     * @param array $expected
     * @throws \Exception
     * @dataProvider getDependencyInfoDataCaseGetUrlDataProvider
     */
    public function testGetDependencyInfoCaseGetUrl(
        string $class,
        string $content,
        array $expected
    ) {
        $file = $this->makeMockFilepath($class);
        $module = $this->getModuleFromClass($class);

        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', $file, $content));
    }

    /**
     * @return array
     */
    public static function getDependencyInfoDataCaseGetUrlDataProvider()
    {
        return [
            'getUrl from same module' => [
                'Magento\Cms\SomeClass',
                '$this->getUrl("cms/index/index")',
                []
            ],
            'getUrl from another module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getUrl("cms/index/index")',
                [
                    [
                        'modules' => ['Magento\Cms'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'getUrl("cms/index/index"',
                    ]
                ]
            ],
            'getUrl from API of same module with parameter' => [
                'Magento\Catalog\SomeClass',
                '$this->getUrl("rest/V1/products/3")',
                []
            ],
            'getUrl from API of same module without parameter' => [
                'Magento\Catalog\SomeClass',
                '$this->getUrl("rest/V1/products")',
                []
            ],
            'getUrl from API of different module with parameter' => [
                'Magento\Backend\SomeClass',
                '$this->getUrl("rest/V1/products/43/options")',
                [
                    [
                        'modules' => ['Magento\Catalog'],
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => 'getUrl("rest/V1/products/43/options"'
                    ]
                ],
            ],
            'getUrl from routeid wildcard' => [
                'Magento\Catalog\Controller\ControllerName\SomeClass',
                '$this->getUrl("*/Invalid/*")',
                []
            ],
            'getUrl from wildcard url within ignored Block class' => [
                'Magento\Cms\Block\SomeClass',
                '$this->getUrl("Catalog/*/View")',
                []
            ],
            'getUrl from wildcard url within ignored Model file' => [
                'Magento\Cms\Model\SomeClass',
                '$this->getUrl("Catalog/*/View")',
                []
            ],
            'getUrl with in admin controller for controllerName wildcard' => [
                'Magento\Backend\Controller\Adminhtml\System\Store\DeleteStore',
                '$this->getUrl("adminhtml/*/deleteStorePost")',
                []
            ],
        ];
    }

    /**
     * @param string $template
     * @param string $content
     * @param array $expected
     * @throws \Exception
     * @dataProvider getDependencyInfoDataCaseGetTemplateUrlDataProvider
     */
    public function testGetDependencyInfoCaseTemplateGetUrl(
        string $template,
        string $content,
        array $expected
    ) {
        $module = $this->getModuleFromClass($template);

        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', $template, $content));
    }

    /**
     * @return array[]
     */
    public static function getDependencyInfoDataCaseGetTemplateUrlDataProvider()
    {
        return [ 'getUrl from ignore template' => [
            'app/code/Magento/Backend/view/adminhtml/templates/dashboard/totalbar/script.phtml',
            '$getUrl("adminhtml/*/ajaxBlock")',
            []]];
    }

    /**
     * @param string $class
     * @param string $content
     * @param array $expected
     * @dataProvider processWildcardUrlDataProvider
     */
    public function testProcessWildcardUrl(
        string $class,
        string $content,
        array $expected
    ) {
        $routeMapper = $this->createMock(RouteMapper::class);
        $routeMapper->expects($this->once())
            ->method('getDependencyByRoutePath')
            ->with(
                $this->equalTo($expected['route_id']),
                $this->equalTo($expected['controller_name']),
                $this->equalTo($expected['action_name'])
            );
        $phpRule = new PhpRule(
            $this->mapRoutes,
            $this->mapLayoutBlocks,
            $this->webApiConfigReader,
            $this->pluginMap,
            $this->whitelist,
            $this->classScanner,
            $routeMapper
        );
        $file = $this->makeMockFilepath($class);
        $module = $this->getModuleFromClass($class);

        $phpRule->getDependencyInfo($module, 'php', $file, $content);
    }

    /**
     * @return array[]
     */
    public static function processWildcardUrlDataProvider()
    {
        return [
            'wildcard controller route' => [
                'Magento\SomeModule\Controller\ControllerName\SomeClass',
                '$this->getUrl("cms/*/index")',
                [
                    'route_id' => 'cms',
                    'controller_name' => 'controllername',
                    'action_name' => 'index'
                ]
            ],
            'adminhtml wildcard controller route' => [
                'Magento\Backend\Controller\Adminhtml\System\Store\DeleteStore',
                '$this->getUrl("adminhtml/*/deleteStorePost")',
                    [
                        'route_id' => 'adminhtml',
                        'controller_name' => 'system_store',
                        'action_name' => 'deletestorepost'
                    ]
            ],
            'index wildcard' => [
                'Magento\Backend\Controller\System\Store\DeleteStore',
                '$this->getUrl("routeid/controllername/*")',
                [
                    'route_id' => 'routeid',
                    'controller_name' => 'controllername',
                    'action_name' => 'deletestore'
                ]
            ]
        ];
    }

    /**
     * @param string $class
     * @param string $content
     * @param \Exception $expected
     * @throws \Exception
     * @dataProvider getDependencyInfoDataCaseGetUrlExceptionDataProvider
     */
    public function testGetDependencyInfoCaseGetUrlException(
        string $class,
        string $content,
        \Exception $expected
    ) {
        $file = $this->makeMockFilepath($class);
        $module = $this->getModuleFromClass($class);
        $this->expectExceptionObject($expected);

        $this->model->getDependencyInfo($module, 'php', $file, $content);
    }

    /**
     * @return array
     */
    public static function getDependencyInfoDataCaseGetUrlExceptionDataProvider()
    {
        return [
            'getUrl from same module' => [
                'Magento\SomeModule\SomeClass',
                '$this->getUrl("someModule")',
                new LocalizedException(__('Invalid URL path: %1', 'somemodule/index/index')),
            ],
            'getUrl from unknown wildcard path' => [
                'Magento\Catalog\Controller\Product\View',
                '$this->getUrl("Catalog/*/INVALID")',
                new LocalizedException(__('Invalid URL path: %1', 'catalog/product/invalid')),
            ],
        ];
    }

    /**
     * @param string $module
     * @param string $content
     * @param array $expected
     * @dataProvider getDefaultModelDependencyDataProvider
     * @throws \Exception
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
        $this->model = new PhpRule([], $mapLayoutBlocks, $this->webApiConfigReader);
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'template', 'any', $content));
    }

    /**
     * @return array
     */
    public static function getDefaultModelDependencyDataProvider()
    {
        return [
            [
                'Magento\AnotherModule',
                '$this->getLayout()->getBlock(\'block.name\');',
                [
                    [
                        'modules' => ['Magento\SomeModule'],
                        'type' => RuleInterface::TYPE_HARD,
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
    private function getModuleFromClass(string $class): string
    {
        $moduleNameLength = strpos($class, '\\', strpos($class, '\\') + 1);
        return substr($class, 0, $moduleNameLength);
    }

    /**
     *  Returns an example list of services that would be parsed via the configReader
     *
     * @return \PHPUnit\Framework\MockObject\MockObject | Filesystem
     */
    private function makeWebApiConfigReaderMock()
    {
        $services = [ 'routes' => [
            '/V1/products/:sku' => [
                'GET' => ['service' => [
                    'class' => 'Magento\Catalog\Api\ProductRepositoryInterface',
                    'method' => 'get'
                ] ],
                'PUT' => ['service' => [
                    'class' => 'Magento\Catalog\Api\ProductRepositoryInterface',
                    'method' => 'save'
                ] ],
            ],
            '/V1/products/:sku/options' => ['GET' => ['service' => [
                'class' => 'Magento\Catalog\Api\ProductCustomOptionRepositoryInterface',
                'method' => 'getList'
            ] ] ],
            '/V1/products' => ['GET' => ['service' => [
                'class' => 'Magento\Catalog\Api\ProductCustomOptionRepositoryInterface',
                'method' => 'getList'
            ] ] ]
        ] ];

        return $this->createConfiguredMock(Filesystem::class, [ 'read' => $services ]);
    }
}
