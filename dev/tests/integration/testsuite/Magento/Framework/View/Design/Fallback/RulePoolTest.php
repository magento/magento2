<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Factory Test
 * @magentoComponentsDir Magento/Framework/View/_files/fallback
 * @magentoDbIsolation enabled
 */
class RulePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RulePool
     */
    protected $model;

    /**
     * @var array
     */
    protected $defaultParams;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $objectManager->get(
            'Magento\Theme\Model\Theme\Registration'
        );
        $registration->register();
        $this->model = $objectManager->create('Magento\Framework\View\Design\Fallback\RulePool');
        /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection $collection */
        $collection = $objectManager->create('Magento\Theme\Model\ResourceModel\Theme\Collection');
        /** @var \Magento\Theme\Model\Theme $theme */
        $theme = $collection->getThemeByFullPath('frontend/Vendor_ViewTest/custom_theme');

        $this->defaultParams = [
            'area' => 'area',
            'theme' => $theme,
            'module_name' => 'ViewTest_Module',
            'locale' => 'en_US',
        ];
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->defaultParams = [];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedException Fallback rule 'unsupported_type' is not supported
     */
    public function testGetRuleUnsupportedType()
    {
        $this->model->getRule('unsupported_type');
    }

    /**
     * @param string $type
     * @param array $overriddenParams
     * @param string $expectedErrorMessage
     *
     * @dataProvider getPatternDirsExceptionDataProvider
     */
    public function testGetPatternDirsException($type, array $overriddenParams, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $params = $overriddenParams + $this->defaultParams;
        $this->model->getRule($type)->getPatternDirs($params);
    }

    /**
     * @return array
     */
    public function getPatternDirsExceptionDataProvider()
    {
        $exceptions = [
            'no theme' => [
                ['theme' => null],
                'Parameter "theme" should be specified and should implement the theme interface',
            ],
            'no area' => [
                ['area' => null],
                "Required parameter 'area' was not passed",
            ],
        ];
        $exceptionsPerTypes = [
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE => [
                'no theme',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE => [
                'no theme', 'no area',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE => [
                'no theme', 'no area',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE => [
                'no theme', 'no area',
            ],
        ];

        $data = [];
        foreach ($exceptionsPerTypes as $type => $exceptionKeys) {
            foreach ($exceptionKeys as $key) {
                $data[$type . ', ' . $key] = [$type, $exceptions[$key][0], $exceptions[$key][1]];
            }
        }

        return $data;
    }

    /**
     * @param string $type
     * @param array $overriddenParams
     * @param array $expectedResult
     *
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($type, array $overriddenParams, array $expectedResult)
    {
        $actualResult = $this->model->getRule($type)
            ->getPatternDirs($overriddenParams + $this->defaultParams);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPatternDirsDataProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar */
        $componentRegistrar = $objectManager->get(
            '\Magento\Framework\Component\ComponentRegistrarInterface'
        );
        $coreModulePath = $componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_Theme');
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $libPath = rtrim($filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');

        $themeOnePath = BP . '/dev/tests/integration/testsuite/Magento/Framework/View/_files/fallback/design/frontend/'
            . 'Vendor/custom_theme';
        $themeTwoPath = BP . '/dev/tests/integration/testsuite/Magento/Framework/View/_files/fallback/design/frontend/'
            . 'Vendor/default';
        $modulePath = BP . '/dev/tests/integration/testsuite/Magento/Framework/View/_files/fallback/app/code/'
            . 'ViewTest_Module';

        return [
            'locale' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE,
                [],
                [
                    $themeOnePath,
                    $themeTwoPath,
                ],
            ],
            'file, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                [],
                [
                    $themeOnePath . '/ViewTest_Module',
                    $themeTwoPath . '/ViewTest_Module',
                    $modulePath . '/view/area',
                    $modulePath . '/view/base',
                ],
            ],
            'file, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                ['namespace' => null, 'module_name' => null],
                [
                    $themeOnePath,
                    $themeTwoPath,
                ],
            ],

            'template, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                [],
                [
                    $themeOnePath . '/ViewTest_Module/templates',
                    $themeTwoPath . '/ViewTest_Module/templates',
                    $modulePath . '/view/area/templates',
                    $modulePath . '/view/base/templates',
                ],
            ],
            'template, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['namespace' => null, 'module_name' => null],
                [
                    $themeOnePath . '/templates',
                    $themeTwoPath . '/templates',
                ],
            ],
            'template, non-modular-magento-core' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['module_name' => 'Magento_Theme'],
                [
                    $themeOnePath . '/Magento_Theme/templates',
                    $themeTwoPath . '/Magento_Theme/templates',
                    $coreModulePath . '/view/area/templates',
                    $coreModulePath . '/view/base/templates',
                ],
            ],

            'view, modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                [],
                [
                    $themeOnePath . '/ViewTest_Module/web/i18n/en_US',
                    $themeOnePath . '/ViewTest_Module/web',
                    $themeTwoPath . '/ViewTest_Module/web/i18n/en_US',
                    $themeTwoPath . '/ViewTest_Module/web',
                    $modulePath . '/view/area/web/i18n/en_US',
                    $modulePath . '/view/base/web/i18n/en_US',
                    $modulePath . '/view/area/web',
                    $modulePath . '/view/base/web',
                ],
            ],
            'view, modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['locale' => null],
                [
                    $themeOnePath . '/ViewTest_Module/web',
                    $themeTwoPath . '/ViewTest_Module/web',
                    $modulePath . '/view/area/web',
                    $modulePath . '/view/base/web',
                ],
            ],
            'view, non-modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module_name' => null],
                [
                    $themeOnePath . '/web/i18n/en_US',
                    $themeOnePath . '/web',
                    $themeTwoPath . '/web/i18n/en_US',
                    $themeTwoPath . '/web',
                    $libPath,
                ],
            ],
            'view, non-modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module_name' => null, 'locale' => null],
                [
                    $themeOnePath . '/web',
                    $themeTwoPath . '/web',
                    $libPath,
                ],
            ],
            // Single test, as emails will always be loaded in a modular context with no locale specificity
            'email' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_EMAIL_TEMPLATE,
                [],
                [
                    $themeOnePath . '/ViewTest_Module/email',
                    $themeTwoPath . '/ViewTest_Module/email',
                    $modulePath . '/view/area/email',
                ],
            ],
        ];
    }
}
